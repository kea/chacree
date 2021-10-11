<?php

declare(strict_types=1);

namespace Kea\Chacree;

class User implements \JsonSerializable
{
    public function __construct(
        private string $id,
        private string $username,
        private string $password,
        private string $avatar = ''
    ) {
        /** @var @todo assert not empty id, username, password */

        $passwordInfo = password_get_info($password);
        if ($passwordInfo['algo'] === null) {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    public static function fromArray(array $user): User
    {
        return new self(
            $user['id'] ?? '',
            $user['username'] ?? '',
            $user['password'] ?? '',
            $user['avatar'] ?? '',
        );
    }

    public function matchPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    /** @return array{id: string, username: string, password: string, avatar: string} */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'avatar' => $this->avatar,
        ];
    }

    public function toArrayForResponse(): array
    {
        $user = $this->jsonSerialize();
        unset($user['password']);

        return $user;
    }
}
