<?php

declare(strict_types=1);

// namespace ;

use davekok\system\NoSuchParameterWiringException;
use davekok\system\NoSuchServiceWiringException;
use davekok\system\NoSuchSetupServiceWiringException;
use davekok\system\Runnable;
use davekok\system\WiringInterface;
use davekok\system\Wirings;

/**
 * A template to quickly write your own wiring.
 */
class Wiring implements WiringInterface
{
    public function infoParameters(): array
    {
        return [
        ];
    }

    public function setParameter(string $key, string|int|float|bool|null $value): void
    {
        match ($key) {
            default => throw new NoSuchParameterWiringException($key),
        };
    }

    public function getParameter(string $key): string|int|float|bool|null
    {
        return match ($key) {
            default => throw new NoSuchParameterWiringException($key),
        };
    }

    public function setup(Wirings $wirings): void
    {
    }

    public function setupService(string $key): object
    {
        return match ($key) {
            default => throw new NoSuchSetupServiceWiringException($key),
        };
    }

    public function wire(Wirings $wirings): Runnable|null
    {
        return null;
    }

    public function service(string $key): object
    {
        return match ($key) {
            default => throw new NoSuchServiceWiringException($key),
        };
    }
}
