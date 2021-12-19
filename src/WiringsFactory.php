<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class WiringsFactory
{
    public function __construct(
        private readonly Component $component,
        private readonly TopologicalSorter $sorter = new TopologicalSorter,
    ) {}

    public function createWirings(): Wirings
    {
        $this->scan($this->component);
        return new Wirings($this->sorter->sort());
    }

    private function scan(Component $component): void
    {
        foreach ($component->getDependencies() as $dependency) {
            $wiring = $dependency->createWiring();
            if ($wiring === null) continue;

            $this->sorter->addNode($dependency->name, $wiring);
            $this->sorter->addDependency($component->name, $dependency->name);

            $this->scan($dependency);
        }
    }
}
