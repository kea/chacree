<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

use Kea\Chacri\Repository\Connections;
use Ramsey\Uuid\Uuid;
use Swoole\WebSocket\Frame;

class CommandFactory
{
    public function __construct(private Connections $connections)
    {
    }

    /**
     * @throws \JsonException
     */
    public function build(Frame $frame): Command
    {
        $event = json_decode($frame->data, true, 512, JSON_THROW_ON_ERROR);
        $data = $event['data'] ?? [];
        $data['id'] = (string)Uuid::uuid6();
        $data['createdAt'] = microtime(true) * 1000;
        $data['senderId'] = $this->getUserId($frame);
        $data['senderFd'] = $frame->fd;

        return match ($event['event']) {
            SendMessage::EVENT_NAME => SendMessage::fromPayload($data),
            Join::EVENT_NAME => Join::fromPayload($data),
            UserInfo::EVENT_NAME => UserInfo::fromPayload($data),
            default => throw new \RuntimeException()
        };
    }

    private function getUserId(Frame $frame): ?string
    {
        return $this->connections->load($frame->fd);
    }
}
