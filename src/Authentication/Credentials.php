<?php

declare(strict_types=1);

namespace Kea\Chacree\Authentication;

use Swoole\Http\Request;

final class Credentials
{
    public function __construct(private string $username, private string $password)
    {
        if (($this->username === '') || $this->password === '') {
            throw new \InvalidArgumentException();
        }
    }

    public static function fromRequest(Request $request): Credentials
    {
        $credentials = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return new self($credentials['username'] ?? '', $credentials['password'] ?? '');
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): string
    {
        return $this->password;
    }
}
