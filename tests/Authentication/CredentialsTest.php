<?php

namespace Kea\Chacree\Authentication;

use PHPUnit\Framework\TestCase;
use Swoole\Http\Request;

/**
 * @covers \Kea\Chacree\Authentication\Credentials
 */
class CredentialsTest extends TestCase
{
    public function testEmptyUerameFailsCreation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new Credentials('', 'password');
    }

    public function testEmptyPasswordFailsCreation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new Credentials('username', '');
    }

    public function testFromRequest()
    {
        $username = 'user';
        $password = 'pass';
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getContent')
            ->willReturn('{"username": "'.$username.'", "password": "'.$password.'"}');

        $credentials = Credentials::fromRequest($request);

        $this->assertSame($username, $credentials->username());
        $this->assertSame($password, $credentials->password());
    }
}
