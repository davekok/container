<?php

declare(strict_types=1);

namespace davekok\container;

/**
 * Interface for container factories.
 */
interface ContainerFactory
{
    public function set(string $key, mixed $value): static;
    public function get(string $key): mixed;
    public function info(): array;
    public function createContainer(): object;
}
