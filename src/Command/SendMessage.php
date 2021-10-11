<?php

declare(strict_types=1);

namespace Kea\Chacree\Command;

class SendMessage implements Command
{
    public const EVENT_NAME = 'send-message';

    public function __construct(
        private string $id,
        private string $message,
        private string $senderId,
        private int $createdAt,
    ) {
    }

    public static function fromPayload(array $payload): SendMessage
    {
        return new self(
            $payload['id'],
            $payload['message'],
            $payload['senderId'],
            (int)$payload['createdAt']
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'senderId' => $this->senderId,
            'createdAt' => $this->createdAt,
        ];
    }
}
