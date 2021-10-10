<?php

namespace Kea\Chacri\Command;

use PHPUnit\Framework\TestCase;

/**
 * @covers Kea\Chacri\Command\CommandHandlerFactory
 */
class CommandHandlerFactoryTest extends TestCase
{
    /**
     * @dataProvider provideCommand
     * @param Command $command
     * @throws \Kea\Chacri\Exception\BadRequest
     */
    public function testBuild(Command $command)
    {
        $sendMessageHandler = $this->getMockBuilder(SendMessageCommandHandler::class)->disableOriginalConstructor()->getMock();
        $joinHandler = $this->getMockBuilder(JoinCommandHandler::class)->disableOriginalConstructor()->getMock();
        $factory = new CommandHandlerFactory($sendMessageHandler, $joinHandler);

        $commandHandler = $factory->build($command);
        $this->assertInstanceOf(CommandHandler::class, $commandHandler);
    }

    public function provideCommand()
    {
        return [
            [new SendMessage('1', 'm', '2', 143200800)],
            [new Join('1', '2', 'c', 143200800)],
        ];
    }
}
