<?php

declare(strict_types=1);

namespace davekok\system;

class NoSuchServiceWiringException extends WiringException
{
    public function __construct(string $key)
    {
        parent::__construct("No such service: $key");
    }
}
