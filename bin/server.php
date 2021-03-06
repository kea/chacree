<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/vendor/autoload.php';

use Kea\Chacree\Authentication\JWT;
use Kea\Chacree\Authentication\UsernameAndPasswordAuthenticator;
use Kea\Chacree\Command\CommandFactory;
use Kea\Chacree\Command\CommandHandlerFactory;
use Kea\Chacree\Command\JoinCommandHandler;
use Kea\Chacree\Command\SendMessageCommandHandler;
use Kea\Chacree\Command\UserInfoCommandHandler;
use Kea\Chacree\Repository\Connections;
use Kea\Chacree\Repository\Messages;
use Kea\Chacree\Repository\Users;
use Kea\Chacree\Server\WebSocketServer;
use Kea\Chacree\UsersOnlineCount;
use Lcobucci\JWT\Signer\Key\InMemory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\WebSocket\Server;

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler(dirname(__DIR__).'/var/log/swoole.log', Logger::DEBUG));

$messages = new Messages(1024);
$connections = new Connections(1024);
$usersOnlineCount = new UsersOnlineCount(0);
$users = new Users(1024);
$users->setLogger($log);

$sendMessageRequestHandler = new SendMessageCommandHandler($messages, $connections);
$joinRequestHandler = new JoinCommandHandler();
$userInfoRequestHandler = new UserInfoCommandHandler($users);

$requestHandlerFactory = new CommandHandlerFactory($sendMessageRequestHandler, $joinRequestHandler, $userInfoRequestHandler);
$commandFactory = new CommandFactory($connections);

/** @todo move to makefile or use other certs */
$keyPair = \sodium_crypto_sign_keypair();
$jwt = new JWT(
    InMemory::plainText(\sodium_crypto_sign_secretkey($keyPair)),
    InMemory::plainText(\sodium_crypto_sign_publickey($keyPair))
);

$authenticator = new UsernameAndPasswordAuthenticator($users, $jwt);

$sslDir = dirname(__DIR__).'/certs';

$sServer = new Server("0.0.0.0", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$sServer->set([
    'ssl_cert_file' => $sslDir.'/https-selfsigned.crt',
    'ssl_key_file' => $sslDir.'/https-selfsigned.key',
    'ssl_ciphers' => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP',
    'document_root' => dirname(__DIR__).'/client',
    'enable_static_handler' => true,
]);

$server = new WebSocketServer(
    $sServer,
    $connections,
    $usersOnlineCount,
    $requestHandlerFactory,
    $commandFactory,
    $authenticator,
    $users
);
$server->setLogger($log);
$server->start();
