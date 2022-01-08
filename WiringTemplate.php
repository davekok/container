<?php

declare(strict_types=1);

// namespace ;

use davekok\wiring\NoSuchParameterWiringException;
use davekok\wiring\NoSuchRunnableWiringException;
use davekok\wiring\NoSuchWireableWiringException;
use davekok\wiring\NoSuchServiceWiringException;
use davekok\wiring\Runnable;
use davekok\wiring\Wiring;
use davekok\wiring\Wirings;

/**
 * A template to quickly write your own wiring.
 */
class MyWiring implements Wiring
{
    private readonly Wirings $wirings;

    public function setWirings(Wirings $wirings): void
    {
        $this->wirings = $wirings;
    }

    public function infoParameters(): array
    {
        return [
        ];
    }

    public function setParameter(string $key, array|string|int|float|bool|null $value): void
    {
        match ($key) {
            default => throw new NoSuchParameterWiringException($key),
        };
    }

    public function getParameter(string $key): array|string|int|float|bool|null
    {
        return match ($key) {
            default => throw new NoSuchParameterWiringException($key),
        };
    }

    public function listRunnables(): array
    {
        return [];
    }

    public function helpRunnable(string $key): string
    {
        return match ($key) {
            default => throw new NoSuchRunnableWiringException();
        };
    }

    public function runnable(string $key, array $args): Runnable
    {
        return match ($key) {
            default => throw new NoSuchRunnableWiringException();
        };
    }

    public function prewire(): void
    {
    }

    public function wireable(string $key): Wireable
    {
        return match ($key) {
            default => throw new NoSuchWireableWiringException($key),
        };
    }

    public function wire(): void
    {
    }

    public function service(string $key): object
    {
        return match ($key) {
            default => throw new NoSuchServiceWiringException($key),
        };
    }
}
