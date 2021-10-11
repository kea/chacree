<?php

declare(strict_types=1);

namespace Kea\Chacree\Repository;

use Kea\Chacree\Authentication\Credentials;
use Kea\Chacree\User;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use Swoole\Table;

class Users extends Table implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(int $tableSize)
    {
        parent::__construct($tableSize);
        $this->column('id', Table::TYPE_STRING, 36);
        $this->column('username', Table::TYPE_STRING, 64);
        $this->column('password', Table::TYPE_STRING, 64);
        $this->column('avatar', Table::TYPE_STRING, 1024);
        $this->create();
    }

    public function register(Credentials $credentials): void
    {
        $this->logger?->info('User::register', [print_r($credentials, true)]);
        $user = new User((string)Uuid::uuid4(), $credentials->username(), $credentials->password());
        $this->save($user);
    }

    public function save(User $user): void
    {
        $this->logger?->info('User::save', [print_r($user->jsonSerialize(), true)]);
        $this->set($user->id(), $user->jsonSerialize());
    }

    public function findById(string $id): ?User
    {
        $userEncoded = $this->get($id);
        if (empty($userEncoded)) {
            return null;
        }

        return User::fromArray($userEncoded);
    }

    public function findByUsernameAndPassword(string $username, string $password): ?User
    {
        foreach ($this as $user) {
            if ($user['username'] !== $username) {
                continue;
            }
            $userToCheck = User::fromArray($user);
            if ($userToCheck->matchPassword($password)) {
                return $userToCheck;
            }
            return null;
        }

        return null;
    }
}
