<?php

declare(strict_types=1);

namespace Kea\Chacree\Response;

use ArrayObject;
use Traversable;

class MultiClientResponse
{
    public function __construct(private string $response, private Traversable|array $clients)
    {
    }

    public function response(): string
    {
        return $this->response;
    }

    public function clients(): Traversable|array
    {
        return $this->clients;
    }

    public static function empty(): self
    {
        return new MultiClientResponse('', new ArrayObject());
    }
}
