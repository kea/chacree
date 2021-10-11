<?php

declare(strict_types=1);

namespace Kea\Chacree\Server;

use Kea\Chacree\Authentication\JWT;
use Kea\Chacree\Authentication\UsernameAndPasswordAuthenticator;
use Kea\Chacree\Command\CommandFactory;
use Kea\Chacree\Command\CommandHandler;
use Kea\Chacree\Command\CommandHandlerFactory;
use Kea\Chacree\Command\SendMessage;
use Kea\Chacree\Repository\Connections;
use Kea\Chacree\Repository\Users;
use Kea\Chacree\Response\MultiClientResponse;
use Kea\Chacree\User;
use Kea\Chacree\UsersOnlineCount;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * @covers \Kea\Chacree\Server\WebSocketServer
 */
class WebSocketServerTest extends TestCase
{
    /**
     * @covers \Kea\Chacree\Response\MultiClientResponse
     * @covers \Kea\Chacree\Command\SendMessage::__construct
     */
    public function testReceiveTheSendMessageCommand(): void
    {
        $frame = new Frame();
        $frame->fd = 10;
        $frame->data = '{ "event": "send-message", "data": { "message": "message text" } }';

        $sendMessageCommand = new SendMessage((string)Uuid::uuid4(), 'message', (string)Uuid::uuid4(), 0);
        $commandHandler = $this->getCommandHandlerMock();
        $swooleServer = $this->getSwooleServerMock();
        $connections = $this->getConnectionsMock();
        $usersOnlineCount = $this->getUsersOnlineCountMock();
        $commandFactory = $this->getCommandFactoryMock($frame, $sendMessageCommand);
        $commandHandlerFactory = $this->getCommandHandlerFactoryMock($sendMessageCommand, $commandHandler);
        $authenticator = $this->getMockBuilder(UsernameAndPasswordAuthenticator::class)->disableOriginalConstructor(
        )->getMock();
        $users = $this->getMockBuilder(Users::class)->disableOriginalConstructor()->getMock();

        $server = new WebSocketServer(
            $swooleServer,
            $connections,
            $usersOnlineCount,
            $commandHandlerFactory,
            $commandFactory,
            $authenticator,
            $users
        );

        $server->onMessage($swooleServer, $frame);
    }

    /**
     * @covers \Kea\Chacree\User::__construct
     * @covers \Kea\Chacree\Authentication\Credentials
     * @covers \Kea\Chacree\Controller\DefaultController
     */
    public function testCreateJWT()
    {
        $swooleServer = $this->getSwooleServerMock();
        $connections = $this->getConnectionsMock();
        $usersOnlineCount = $this->getUsersOnlineCountMock();
        $commandHandlerFactory = $this->getMockBuilder(CommandHandlerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandFactory = $this->getMockBuilder(CommandFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authenticator = $this->getMockBuilder(UsernameAndPasswordAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authenticator->method('authenticate')
            ->willReturn(new User('1', 'username', 'plainPassword'));
        $authenticator->method('createToken')
            ->willReturn('token');

        $users = $this->getMockBuilder(Users::class)->disableOriginalConstructor()->getMock();

        $server = new WebSocketServer(
            $swooleServer,
            $connections,
            $usersOnlineCount,
            $commandHandlerFactory,
            $commandFactory,
            $authenticator,
            $users
        );

        $body = json_encode(['username' => 'kea', 'password' => 'kabooom'], JSON_THROW_ON_ERROR);
        $request = $this->buildSwooleRequest('/sessions', 'post', $body);

        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $response
            ->expects($this->once())
            ->method('header')
            ->with('Content-Type', 'application/json');
        $response
            ->expects($this->once())
            ->method('end')
            ->willReturnCallback(function ($content) {
                $response = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                $this->assertSame('token', $response['token']);
            });

        $server->onRequest($request, $response);
    }

    private function buildSwooleRequest(string $uri, string $method = 'get', string $postBody = null): Request
    {
        $swooleRequest = $this->getMockBuilder(Request::class)->getMock();
        $swooleRequest->server = [
            'request_method' => $method,
            'request_uri' => $uri,
            'server_protocol' => 'HTTP/1.1',
        ];
        $swooleRequest->header = ['host' => 'localhost'];
        $swooleRequest->post = $postBody;

        $swooleRequest
            ->expects($this->any())
            ->method('rawContent')
            ->willReturn($postBody);
        $swooleRequest
            ->expects($this->any())
            ->method('getContent') // is an alias of rawContent
            ->willReturn($postBody);
        $swooleRequest
            ->expects($this->any())
            ->method('getMethod')
            ->willReturn(strtoupper($method));

        return $swooleRequest;
    }

    /**
     * @return CommandHandler|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCommandHandlerMock(): mixed
    {
        $commandHandler = $this->getMockBuilder(CommandHandler::class)->getMockForAbstractClass();
        $commandHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturn(MultiClientResponse::empty());

        return $commandHandler;
    }

    /**
     * @return mixed|\PHPUnit\Framework\MockObject\MockObject|Server
     */
    protected function getSwooleServerMock(): mixed
    {
        $swooleServer = $this->getMockBuilder(Server::class)->disableOriginalConstructor()->getMock();

        return $swooleServer;
    }

    /**
     * @return Connections|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConnectionsMock(): mixed
    {
        return $this->getMockBuilder(Connections::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return UsersOnlineCount|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getUsersOnlineCountMock(): mixed
    {
        $usersOnlineCount = $this->getMockBuilder(UsersOnlineCount::class)->disableOriginalConstructor()->getMock();

        return $usersOnlineCount;
    }

    /**
     * @param SendMessage $sendMessageCommand
     * @param mixed       $commandHandler
     * @return CommandHandlerFactory|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCommandHandlerFactoryMock(SendMessage $sendMessageCommand, mixed $commandHandler): mixed
    {
        $commandHandlerFactory = $this->getMockBuilder(CommandHandlerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandHandlerFactory
            ->expects($this->once())
            ->method('build')
            ->with($sendMessageCommand)
            ->willReturn($commandHandler);

        return $commandHandlerFactory;
    }

    /**
     * @param Frame       $frame
     * @param SendMessage $sendMessageCommand
     * @return CommandFactory|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCommandFactoryMock(Frame $frame, SendMessage $sendMessageCommand): mixed
    {
        $commandFactory = $this->getMockBuilder(CommandFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandFactory
            ->expects($this->once())
            ->method('build')
            ->with($frame)
            ->willReturn($sendMessageCommand);

        return $commandFactory;
    }

    /**
     * @return JWT|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getJwtAuthenticatorMock(): mixed
    {
        $jwtAuthenticator = $this->getMockBuilder(JWT::class)->disableOriginalConstructor()->getMock();
        $jwtAuthenticator
            ->expects($this->once())
            ->method('createToken')
            ->willReturn('token');

        return $jwtAuthenticator;
    }
}
