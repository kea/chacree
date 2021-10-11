<?php

namespace Kea\Chacree\Command;

use Kea\Chacree\Repository\Connections;
use PHPUnit\Framework\TestCase;
use Swoole\WebSocket\Frame;

/**
 * @covers Kea\Chacree\Command\CommandFactory
 * @covers Kea\Chacree\Command\SendMessage
 */
class CommandFactoryTest extends TestCase
{
    public function testEventType()
    {
        $connections = $this->getMockBuilder(Connections::class)->disableOriginalConstructor()->getMock();
        $connections->method('load')->willReturn('UserIdUserID');
        $factory = new CommandFactory($connections);

        $frame = new Frame();
        $frame->fd = 10;
        $frame->data = '{ "event": "send-message", "data": { "message": "message text" } }';

        $command = $factory->build($frame);
        $this->assertInstanceOf(SendMessage::class, $command);
    }
}
