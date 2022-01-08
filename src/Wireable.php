<?php

declare(strict_types=1);

namespace davekok\wiring;

/**
 * Base interface of wireables.
 */
interface Wireable
{
    /**
     * Wire the wireable. Depending on context may return something or nothing.
     */
    public function wire();
}
