<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

use Kea\Chacri\Exception\BadRequest;

class CommandHandlerFactory
{
    public function __construct(
        private SendMessageCommandHandler $sendMessageCommandHandler,
        private JoinCommandHandler $joinCommandHandler,
        private UserInfoCommandHandler $userInfoCommandHandler
    ) {
    }

    /**
     * @throws BadRequest
     */
    public function build(Command $command): CommandHandler
    {
        return match (get_class($command)) {
            SendMessage::class => $this->sendMessageCommandHandler,
            Join::class => $this->joinCommandHandler,
            UserInfo::class => $this->userInfoCommandHandler,
            default => throw new BadRequest()
        };
    }
}
