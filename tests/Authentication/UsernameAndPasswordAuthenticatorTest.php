<?php

namespace Kea\Chacree\Authentication;

use Kea\Chacree\Exception\Unauthorized;
use Kea\Chacree\Repository\Users;
use Kea\Chacree\User;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kea\Chacree\Authentication\UsernameAndPasswordAuthenticator
 */
class UsernameAndPasswordAuthenticatorTest extends TestCase
{
    /**
     * @covers \Kea\Chacree\Authentication\Credentials
     */
    public function testAuthSearchUserOnRepository()
    {
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $users = $this->getMockBuilder(Users::class)->disableOriginalConstructor()->getMock();
        $users->method('findByUsernameAndPassword')
            ->willReturnOnConsecutiveCalls($user, null);

        $jwt = $this->getMockBuilder(JWT::class)->disableOriginalConstructor()->getMock();

        $authenticator = new UsernameAndPasswordAuthenticator($users, $jwt);

        $this->assertSame($user, $authenticator->authenticate(new Credentials('user', 'pass')));

        $this->expectException(Unauthorized::class);
        $authenticator->authenticate(new Credentials('user', 'pass'));
    }

    /**
     * @covers \Kea\Chacree\User
     */
    public function testCreateTokenIsProxyToJWT()
    {
        $user = new User('1', 'u', 'p', 'a');
        $users = $this->getMockBuilder(Users::class)->disableOriginalConstructor()->getMock();
        $jwt = $this->getMockBuilder(JWT::class)->disableOriginalConstructor()->getMock();
        $jwt->method('createToken')
            ->with($user->id(), $user->username())
            ->willReturn('myToken');

        $authenticator = new UsernameAndPasswordAuthenticator($users, $jwt);

        $this->assertSame('myToken', $authenticator->createToken($user));
    }

    public function testDecryptTokenIsProxyToJWT()
    {
        $stringToken = 'someTestToPass';
        $unecryptedToken = $this->getMockBuilder(UnencryptedToken::class)->getMock();
        $users = $this->getMockBuilder(Users::class)->disableOriginalConstructor()->getMock();
        $jwt = $this->getMockBuilder(JWT::class)->disableOriginalConstructor()->getMock();
        $jwt->method('decryptToken')
            ->with($stringToken)
            ->willReturn($unecryptedToken);

        $authenticator = new UsernameAndPasswordAuthenticator($users, $jwt);

        $this->assertSame($unecryptedToken, $authenticator->decryptToken($stringToken));
    }
}
