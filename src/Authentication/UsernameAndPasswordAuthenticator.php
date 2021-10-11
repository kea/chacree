<?php

declare(strict_types=1);

namespace Kea\Chacree\Authentication;

use Kea\Chacree\Exception\InvalidToken;
use Kea\Chacree\Exception\Unauthorized;
use Kea\Chacree\Repository\Users;
use Kea\Chacree\User;
use Lcobucci\JWT\UnencryptedToken;

class UsernameAndPasswordAuthenticator
{
    public function __construct(private Users $users, private JWT $jwt)
    {
    }

    /**
     * @throws Unauthorized
     */
    public function authenticate(Credentials $credentials): User
    {
        $user = $this->users->findByUsernameAndPassword($credentials->username(), $credentials->password());

        if ($user === null) {
            throw new Unauthorized();
        }

        return $user;
    }

    public function createToken(User $user): string
    {
        return $this->jwt->createToken($user->id(), $user->username());
    }

    /**
     * @throws InvalidToken
     */
    public function decryptToken(string $encryptedToken): UnencryptedToken
    {
        return $this->jwt->decryptToken($encryptedToken);
    }
}
