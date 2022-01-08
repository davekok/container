<?php

declare(strict_types=1);

namespace davekok\wiring;

/**
 * Interface that components can implement for wiring.
 */
interface Wiring extends Wireable
{
    /**
     * The wirings object gives access to other wirings.
     */
    public function setWirings(Wirings $wirings): void;

    /**
     * Parameters available in the component.
     */
    public function infoParameters(): array;

    /**
     * Set a parameter.
     */
    public function setParameter(string $key, string|int|float|bool|array|null $value): void;

    /**
     * Get a parameter.
     */
    public function getParameter(string $key): string|int|float|bool|array|null;

    /**
     * Return a list of runnables.
     */
    public function listRunnables(): array;

    /**
     * Return help string for the runnable.
     */
    public function helpRunnable(string $key): string;

    /**
     * Get a runnable from the component.
     */
    public function runnable(string $key, array $args): Runnable;

    /**
     * Called during the prewire phase.
     */
    public function prewire(): void;

    /**
     * Get a wireable provided by the component.
     *
     * Wireables can be used for more fine tuned wiring.
     */
    public function wireable(string $key): Wireable;

    /**
     * Called during the wire phase.
     */
    public function wire(): void;

    /**
     * Get a service provided by the component.
     */
    public function service(string $key): object;
}
