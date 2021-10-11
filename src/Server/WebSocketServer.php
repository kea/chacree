<?php

declare(strict_types=1);

namespace Kea\Chacree\Server;

use Kea\Chacree\Authentication\UsernameAndPasswordAuthenticator;
use Kea\Chacree\Command\CommandFactory;
use Kea\Chacree\Controller\DefaultController;
use Kea\Chacree\Exception\InvalidToken;
use Kea\Chacree\Repository\Connections;
use Kea\Chacree\Command\CommandHandlerFactory;
use Kea\Chacree\Repository\Users;
use Kea\Chacree\Response\MultiClientResponse;
use Kea\Chacree\UsersOnlineCount;
use Psr\Log\LoggerAwareTrait;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketServer
{
    use LoggerAwareTrait;

    private const RFC_6455_SEC_KEY_SECRET_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public function __construct(
        private Server $server,
        private Connections $connections,
        private UsersOnlineCount $usersOnlineCount,
        private CommandHandlerFactory $commandHandlerFactory,
        private CommandFactory $commandFactory,
        private UsernameAndPasswordAuthenticator $authenticator,
        private Users $users
    ) {
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('Request', [$this, 'onRequest']);
    }

    public function onOpen(Server $server, Request $request): void
    {
        $this->logger?->info('onOpen: '.$request->fd);

        try {
            $token = $this->authenticator->decryptToken($this->getTokenFromRequest($request));
            $this->logger?->info($token->toString());
        } catch (InvalidToken $e) {
            $this->logger?->warning("Client failed to connect: ".$e->getMessage());
            $server->disconnect($request->fd, SWOOLE_WEBSOCKET_CLOSE_POLICY_ERROR, "Unauthorized");
        }

        $fd = (string)$request->fd;
        $this->logger?->info("onOpen success with fd $fd and userId {$token->claims()->get('userId')}");
        $this->connections->set($fd, ['client' => $fd, 'userId' => $token->claims()->get('userId')]);
        $this->usersOnlineCount->set($this->connections->uniqueUserCount());
        $this->connections->notifyEveryone($this->server, $this->usersOnlineCount);
    }

    public function onMessage(Server $server, Frame $frame): void
    {
        $this->logger?->info("onMessage from $frame->fd:$frame->data,opcode:$frame->opcode,fin:$frame->finish");
        try {
            $command = $this->commandFactory->build($frame);
            $commandHandler = $this->commandHandlerFactory->build($command);
            $response = $commandHandler->handle($command);
            $this->send($server, $response);
        } catch (\Throwable $e) {
            $this->logger?->info("onMessage error: ".$e->getMessage());
            $server->disconnect($frame->fd);
        }
    }

    public function onClose(Server $server, int $fd): void
    {
        if (!$this->connections->exists((string)$fd)) {
            return;
        }

        $this->logger?->info("onClose client $fd closed");
        $this->connections->del((string)$fd);
        $this->usersOnlineCount->set($this->connections->uniqueUserCount());

        $usersOnlineCountMessage = json_encode(
            ['type' => 'online-users', 'count' => $this->usersOnlineCount->get()],
            JSON_THROW_ON_ERROR
        );
        foreach ($this->connections as $client) {
            $server->push($client['client'], $usersOnlineCountMessage);
        }
    }

    public function onRequest(Request $request, Response $response): void
    {
        (new DefaultController($this->users, $this->authenticator, $this->logger))->handleRequest($request, $response);
    }

    public function start(): void
    {
        $this->server->start();
    }

    private function send(Server $server, MultiClientResponse $response): void
    {
        foreach ($response->clients() as $client) {
            $server->push($client, $response->response());
        }
    }

    private function getTokenFromRequest(Request $request): string
    {
        return $request->get['token'] ?? '';
    }
}
