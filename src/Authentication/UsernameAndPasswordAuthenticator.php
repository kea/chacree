<?php

declare(strict_types=1);

namespace Kea\Chacri\Authentication;

use Kea\Chacri\Exception\InvalidToken;
use Kea\Chacri\Exception\Unauthorized;
use Kea\Chacri\Repository\Users;
use Kea\Chacri\User;
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
