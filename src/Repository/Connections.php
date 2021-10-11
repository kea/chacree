<?php

declare(strict_types=1);

namespace Kea\Chacree\Repository;

use Kea\Chacree\User;
use Swoole\Table;
use Swoole\WebSocket\Server;

class Connections extends Table
{
    public function __construct(int $tableSize)
    {
        parent::__construct($tableSize);
        $this->column('client', Table::TYPE_INT, 4);
        $this->column('userId', Table::TYPE_STRING, 64);
        $this->create();
    }

    public function save(int $client, User $user): void
    {
        $this->set((string)$client, ['client' => $client, 'userId' => $user->id()]);
    }

    public function load(int $client): ?string
    {
        return $this->get((string)$client, 'userId');
    }

    public function notifyEveryone(Server $server, \JsonSerializable $message): void
    {
        $payload = json_encode($message, JSON_THROW_ON_ERROR);
        foreach ($this as $connection) {
            $server->push($connection['client'], $payload);
        }
    }

    /**
     * @return int[] clients fd
     */
    public function getAllClients(): array
    {
        $connections = [];
        foreach ($this as $connection) {
            $connections[] = $connection['client'];
        }

        return $connections;
    }

    public function uniqueUserCount(): int
    {
        $connections = [];
        foreach ($this as $connection) {
            $connections[$connection['userId']] = true;
        }

        return count($connections);
    }
}
