<?php

declare(strict_types=1);

namespace Kea\Chacri;

use Kea\Chacri\Command\SendMessage;

class Message implements \JsonSerializable
{
    public function __construct(
        private string $id,
        private string $message,
        private string $senderId,
        private int $createdAt,
    ) {
    }

    public static function fromCommand(SendMessage $sendMessage): Message
    {
        $message = $sendMessage->jsonSerialize();

        return new self(
            $message['id'],
            $message['message'],
            $message['senderId'],
            (int)$message['createdAt'],
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => 'message',
            'data' => $this->toArrayForSave(),
        ];
    }

    public function toArrayForSave(): array
    {
        return [
            'id' => $this->id,
            'senderId' => $this->senderId,
            'message' => $this->message,
            'createdAt' => $this->createdAt,
        ];
    }
}
