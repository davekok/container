<?php

declare(strict_types=1);

namespace davekok\system;

/**
 * Interface for runnable objects.
 */
interface Runnable
{
    public function run(): never;
}
