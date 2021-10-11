<?php

declare(strict_types=1);

namespace Kea\Chacree\Command;

use Kea\Chacree\Response\MultiClientResponse;

interface CommandHandler
{
    public function handle(Command $command): MultiClientResponse;
}
