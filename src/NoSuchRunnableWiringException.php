<?php

declare(strict_types=1);

namespace davekok\wiring;

class NoSuchRunnableWiringException extends WiringException
{
    public function __construct(string $key)
    {
        parent::__construct("No such runnable: $key");
    }
}
