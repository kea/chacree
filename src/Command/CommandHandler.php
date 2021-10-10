<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

use Kea\Chacri\Response\MultiClientResponse;

interface CommandHandler
{
    public function handle(Command $command): MultiClientResponse;
}
