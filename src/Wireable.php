<?php

declare(strict_types=1);

namespace davekok\system;

interface Wireable
{
    public function wire(Wirings $wirings): mixed;
}
