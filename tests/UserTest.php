<?php

declare(strict_types=1);

namespace Kea\Chacree;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @covers \Kea\Chacree\User
     */
    public function testFromArray(): void
    {
        $password = password_hash('myStrongPassword', PASSWORD_DEFAULT);
        $userAsArray = ['id' => '1', 'username' => 'u', 'password' => $password, 'avatar' => 'a'];
        $user1 = User::fromArray($userAsArray);
        $user2 = new User('1', 'u', $password, 'a');

        $this->assertEquals($user1, $user2);
        $this->assertSame($userAsArray['id'], $user1->id());
        $this->assertSame($userAsArray['username'], $user1->username());
    }

    /**
     * @covers \Kea\Chacree\User
     */
    public function testHashedPasswordIsStoredAsIs(): void
    {
        $password = password_hash('myStrongPassword', PASSWORD_DEFAULT);
        $user = new User('1', 'u', $password, 'a');
        $userAsArray = $user->jsonSerialize();

        $this->assertSame($password, $userAsArray['password']);
    }

    /**
     * @covers \Kea\Chacree\User
     */
    public function testPlainPasswordIsHashed(): void
    {
        $user = new User('1', 'u', 'plainPassword', 'a');
        $userAsArray = $user->jsonSerialize();

        $passwordInfo = password_get_info($userAsArray['password']);
        $this->assertNotNull($passwordInfo['algo']);
    }

    /**
     * @covers \Kea\Chacree\User
     */
    public function testMatchPassword(): void
    {
        $plainPassword = 'plainPassword';
        $user = new User('1', 'u', $plainPassword, 'a');

        $this->assertTrue($user->matchPassword($plainPassword));
        $this->assertFalse($user->matchPassword('anotherPassword'));
    }
}
