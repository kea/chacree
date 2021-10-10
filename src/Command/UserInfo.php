<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

class UserInfo implements Command
{
    public const EVENT_NAME = 'user-info';

    public function __construct(
        private string $userId,
        private int $senderFd
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            $payload['userId'],
            (int)$payload['senderFd']
        );
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function senderFd(): int
    {
        return $this->senderFd;
    }

    public function jsonSerialize()
    {
        return [
            'userId' => $this->userId,
            'senderFd' => $this->senderFd,
        ];
    }
}
