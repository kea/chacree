<?php

namespace Kea\Chacree\Command;

use PHPUnit\Framework\TestCase;

/**
 * @covers Kea\Chacree\Command\Join
 */
class JoinTest extends TestCase
{
    public function testFromPayload()
    {
        $payload = [
            "channel" => 'C123',
            'id' => '1234',
            'senderId' => 'user1234',
            'createdAt' => 100000,
        ];

        $join = Join::fromPayload($payload);

        $serialized = $join->jsonSerialize();
        $this->assertSame($payload['channel'], $serialized['channel']);
        $this->assertSame('1234', $serialized['id']);
        $this->assertSame('1234', $join->id());
        $this->assertSame('user1234', $serialized['senderId']);
        $this->assertSame(100000, $serialized['createdAt']);
    }
}
