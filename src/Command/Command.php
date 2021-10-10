<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

interface Command extends \JsonSerializable
{
    public static function fromPayload(array $payload): self;
}
