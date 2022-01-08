<?php

declare(strict_types=1);

namespace davekok\wiring;

use Exception;
use Throwable;

class WiringsFactory
{
    public function __construct(
        private readonly Component $component,
        private readonly TopologicalDependencySorter $sorter = new TopologicalDependencySorter,
    ) {}

    public function createWirings(): Wirings
    {
        $this->scan($this->component); // recursive function

        $wirings = [];
        foreach ($this->sorter->sort() as $key => $wiring) {
            [$vendor, $component] = explode("/", $key);
            $wirings[$vendor][$component] = $wiring;
        }

        return new Wirings($wirings);
    }

    private function scan(Component $component): void
    {
        foreach ($component->getDependencies() as $dependency) {
            if ($this->sorter->hasNode($dependency->name) === false) {
                $wiring = $dependency->createWiring();
                if ($wiring === null) continue;
                $this->sorter->addNode($dependency->name, $wiring);
            }

            $this->sorter->addDependency($component->name, $dependency->name);

            $this->scan($dependency);
        }
    }
}
