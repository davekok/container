<?php

declare(strict_types=1);

namespace davekok\wiring;

use Exception;
use Throwable;

class Wirings
{
    public function __construct(
        private readonly array $wirings = [],
    ) {
        foreach ($this->wirings as $components) {
            foreach ($components as $wiring) {
                $wiring->setWirings($this);
            }
        }
    }

    public function get(string $vendor, string $component): Wiring
    {
        return $this->wirings[$vendor][$component] ?? throw new WiringException("Not found: $vendor/$component");
    }

    public function all(): array
    {
        return $this->wirings;
    }

    public function infoParameters(): array
    {
        $info = [];
        foreach ($this->wirings as $vendor => $components) {
            foreach ($components as $component => $wiring) {
                $info[$vendor][$component] = $wiring->infoParameters();
            }
        }
        return $info;
    }

    public function setParameter(string $vendor, string $component, string $parameter, string|int|float|bool|array|null $value): self
    {
        $this->get($vendor, $component)->setParameter($parameter, $value);
        return $this;
    }

    public function getParameter(string $vendor, string $component, string $parameter): string|int|float|bool|array|null
    {
        return $this->get($vendor, $component)->getParameter($parameter);
    }

    public function wireable(string $vendor, string $component, string $wireable): object
    {
        return $this->get($vendor, $component)->wireable($wireable);
    }

    public function service(string $vendor, string $component, string $service): object
    {
        return $this->get($vendor, $component)->service($service);
    }

    public function helpRunnable(string $vendor, string $component, string $runnable): string
    {
        return $this->get($vendor, $component)->helpRunnable($runnable);
    }

    public function runnable(string $vendor, string $component, string $runnable, array $args): Runnable
    {
        return $this->get($vendor, $component)->runnable($runnable, $args);
    }
}
