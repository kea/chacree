<?php

declare(strict_types=1);

namespace Kea\Chacree\Repository;

use Kea\Chacree\Message;
use Swoole\Table;

class Messages extends Table
{
    public function __construct(int $tableSize)
    {
        parent::__construct($tableSize);
        $this->column('id', Table::TYPE_STRING, 36);
        $this->column('client', Table::TYPE_INT, 4);
        $this->column('senderId', Table::TYPE_STRING, 36);
        $this->column('username', Table::TYPE_STRING, 64);
        $this->column('message', Table::TYPE_STRING, 1024);
        $this->create();
    }

    public function save(Message $message): void
    {
        $this->set($message->id(), $message->toArrayForSave());
    }
}
