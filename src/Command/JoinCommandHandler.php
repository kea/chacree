<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

use Kea\Chacri\Response\MultiClientResponse;

class JoinCommandHandler implements CommandHandler
{
    public function __construct()
    {
    }

    public function handle(Command $command): MultiClientResponse
    {
        /** @todo Join to a Room. Room? Which Room?!?! */

        return MultiClientResponse::empty();
    }
}
