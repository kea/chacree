<?php

declare(strict_types=1);

namespace Kea\Chacri;

class UsersOnlineCount extends \Swoole\Atomic implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return ['event' => 'online-users', 'data' => ['count' => $this->get()]];
    }
}
