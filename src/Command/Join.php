<?php

declare(strict_types=1);

namespace Kea\Chacree\Command;

class Join implements Command
{
    public const EVENT_NAME = 'join-channel';

    public function __construct(
        private string $id,
        private string $senderId,
        private string $channel,
        private int $createdAt
    ) {
    }

    public static function fromPayload(array $payload): Join
    {
        return new self(
            $payload['id'],
            $payload['senderId'],
            $payload['channel'],
            (int)$payload['createdAt']
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'senderId' => $this->senderId,
            'channel' => $this->channel,
            'createdAt' => $this->createdAt,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }
}
