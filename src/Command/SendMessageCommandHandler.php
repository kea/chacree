<?php

declare(strict_types=1);

namespace Kea\Chacree\Command;

use Exception;
use Kea\Chacree\Message;
use Kea\Chacree\Repository\Connections;
use Kea\Chacree\Repository\Messages;
use Kea\Chacree\Response\MultiClientResponse;

class SendMessageCommandHandler implements CommandHandler
{
    public function __construct(private Messages $messages, private Connections $connections)
    {
    }

    /**
     * @throws \JsonException
     * @throws Exception
     */
    public function handle(Command $command): MultiClientResponse
    {
        if (!$command instanceof SendMessage) {
            throw new Exception();
        }

        $message = Message::fromCommand($command);
        $this->messages->save($message);

        return new MultiClientResponse(json_encode($message, JSON_THROW_ON_ERROR), $this->connections->getAllClients());
    }
}
