<?php

declare(strict_types=1);

namespace Kea\Chacri\Response;

use Kea\Chacri\User;

class UserInfo implements \JsonSerializable
{
    public function __construct(private User $user)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'event' => 'user-info',
            'data' => $this->user->toArrayForResponse(),
        ];
    }
}
