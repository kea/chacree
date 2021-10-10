<?php

namespace Kea\Chacri\Command;

use PHPUnit\Framework\TestCase;

/**
 * @covers Kea\Chacri\Command\SendMessage
 */
class SendMessageTest extends TestCase
{
    public function testFromPayload()
    {
        $payload = [
            "message" => "message text",
            'id' => '1234',
            'senderId' => 'user1234',
            'createdAt' => 100000,
        ];

        $sendMessage = SendMessage::fromPayload($payload);

        $serialized = $sendMessage->jsonSerialize();
        $this->assertSame($payload['message'], $serialized['message']);
        $this->assertSame('1234', $serialized['id']);
        $this->assertSame('1234', $sendMessage->id());
        $this->assertSame('user1234', $serialized['senderId']);
        $this->assertSame(100000, $serialized['createdAt']);
    }
}
