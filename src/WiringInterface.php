<?php

declare(strict_types=1);

namespace davekok\system;

/**
 * Interface that components can implement for wiring.
 */
interface WiringInterface
{
    /**
     * Parameters available in this component.
     */
    public function infoParameters(): array;

    /**
     * Set a parameter.
     */
    public function setParameter(string $key, string|int|float|bool|null $value): void;

    /**
     * Get a parameter.
     */
    public function getParameter(string $key): string|int|float|bool|null;

    /**
     * Configure wire services.
     *
     * The wirings argument gives access to wirings of other components.
     */
    public function prewire(Wirings $wirings): void;

    /**
     * Get a wire service provided by the component.
     */
    public function wireService(string $key): object;

    /**
     * Wire component.
     *
     * The wirings argument gives access to wirings of other components.
     *
     * Either return nothing or a Runnable. Only one Wiring can return a Runnable. If a Runnable is returned
     * it is run after wiring is complete.
     */
    public function wire(Wirings $wirings): Runnable|null;

    /**
     * Get a service provided by the component.
     */
    public function service(string $key): object;
}
