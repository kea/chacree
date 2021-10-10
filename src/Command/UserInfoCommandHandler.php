<?php

declare(strict_types=1);

namespace Kea\Chacri\Command;

use Exception;
use Kea\Chacri\Exception\BadRequest;
use Kea\Chacri\Repository\Users;
use Kea\Chacri\Response\MultiClientResponse;
use Kea\Chacri\Response\UserInfo as UserInfoResponse;
use Kea\Chacri\User;

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
