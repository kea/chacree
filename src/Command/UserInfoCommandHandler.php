<?php

declare(strict_types=1);

namespace Kea\Chacree\Command;

use Exception;
use Kea\Chacree\Exception\BadRequest;
use Kea\Chacree\Repository\Users;
use Kea\Chacree\Response\MultiClientResponse;
use Kea\Chacree\Response\UserInfo as UserInfoResponse;
use Kea\Chacree\User;

class UserInfoCommandHandler implements CommandHandler
{
    public function __construct(private Users $users)
    {
    }

    public function handle(Command $command): MultiClientResponse
    {
        if (!$command instanceof UserInfo) {
            throw new Exception();
        }

        $user = $this->users->findById($command->userId());

        if (!$user instanceof User) {
            throw new BadRequest();
        }

        $userInfo = new UserInfoResponse($user);

        return new MultiClientResponse(json_encode($userInfo, JSON_THROW_ON_ERROR), [$command->senderFd()]);
    }
}
