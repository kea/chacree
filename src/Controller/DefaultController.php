<?php

declare(strict_types=1);

namespace Kea\Chacri\Controller;

use Kea\Chacri\Authentication\Credentials;
use Kea\Chacri\Authentication\UsernameAndPasswordAuthenticator;
use Kea\Chacri\Exception\InvalidToken;
use Kea\Chacri\Repository\Users;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class DefaultController
{
    public function __construct(
        private Users $users,
        private UsernameAndPasswordAuthenticator $authenticator,
        private ?LoggerInterface $logger
    ) {
    }

    public function handleRequest(Request $request, Response $response): void
    {
        $method = strtoupper($request->getMethod());
        $this->logger?->info("onRequest {$request->server['request_uri']} [$method]");
        if ($request->server['request_uri'] === '/' && $method === 'GET') {
            $this->clientPage($response);
        } elseif ($request->server['request_uri'] === '/sessions' && $method === 'POST') {
            $this->login($request, $response);
        } elseif ($request->server['request_uri'] === '/users' && $method === 'POST') {
            $this->signup($request, $response);
        } elseif ($request->server['request_uri'] === '/me' && $method === 'GET') {
            $this->me($request, $response);
        } else {
            $response->status(404);
            $response->header('Content-Type', 'application/json');
            $response->end();
        }
    }

    private function login(Request $request, Response $response): void
    {
        $response->header('Content-Type', 'application/json');

        try {
            $credentials = Credentials::fromRequest($request);
            $user = $this->authenticator->authenticate($credentials);
            $token = $this->authenticator->createToken($user);
            $responseBody = json_encode(['token' => $token], JSON_THROW_ON_ERROR);

            $response->end($responseBody);
        } catch (\Throwable $e) {
            echo $e->getMessage();
            $response->status(400);
            $response->end('{ "message": "Invalid username or password" }');
        }
    }

    private function signup(Request $request, Response $response): void
    {
        $response->header('Content-Type', 'application/json');

        try {
            $credentials = Credentials::fromRequest($request);
            $this->users->register($credentials);
            $responseBody = json_encode(['message' => 'ok'], JSON_THROW_ON_ERROR);

            $response->end($responseBody);
        } catch (\Throwable) {
            $response->status(400);
            $response->end('{ "message": "Registration failed... sorry ;(" }');
        }
    }

    private function me(Request $request, Response $response): void
    {
        try {
            $token = $this->authenticator->decryptToken($request->header['x-auth-token'] ?? '');
        } catch (InvalidToken $e) {
            $response->status(403);

            return;
        }
        $userId = $token->claims()->get('userId');
        $user = $this->users->findById($userId);

        $response->end(json_encode($user, JSON_THROW_ON_ERROR));
    }

    private function clientPage(Response $response): void
    {
        $response->header('Content-Type', 'text/html');
        $response->end(file_get_contents(__DIR__.'/../../client/index.html'));
    }
}
