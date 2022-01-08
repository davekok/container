<?php

declare(strict_types=1);

namespace davekok\wiring;

class NoSuchParameterWiringException extends WiringException
{
    public function __construct(string $key)
    {
        parent::__construct("No such parameter: $key");
    }
}
