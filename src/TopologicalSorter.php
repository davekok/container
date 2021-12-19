<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class TopologicalSorter
{
    private array $nodes        = [];
    private array $dependencies = [];

    /**
     * Add a node.
     */
    public function addNode(string|int $key, mixed $value): void
    {
        $this->nodes[$key] = $value;
    }

    /**
     * Add a dependency for a node.
     */
    public function addDependency(string|int $node, string|int $dependency): void
    {
        $this->dependencies[] = [$node, $dependency];
    }

    /**
     * Return the nodes in sorted order.
     */
    public function sort(): array
    {
        $sorted = [];

        // Remove dependencies without nodes
        foreach ($this->dependencies as $ix => [$node, $dependency]) {
            if (isset($this->nodes[$node]) === false || isset($this->nodes[$dependency]) === false) {
                unset($this->dependencies[$ix]);
            }
        }

        while (count($this->nodes) > 0) {

            // Extract the node column from two-dimensional dependencies array.
            $nodeColumn = array_column($this->dependencies, 0);

            $count = count($this->nodes);
            // Add nodes to sorted that are no longer in nodeColumn.
            foreach ($this->nodes as $key => $value) {
                if (in_array($key, $nodeColumn) === false) {
                    $sorted[$key] = $value;
                    unset($this->nodes[$key]);
                }
            }

            // Make sure at least something has been removed.
            if ($count === count($this->nodes)) throw new Exception("Sort failed");


            // If dependencies are empty just continue adding nodes.
            $count = count($this->dependencies);
            if ($count === 0) continue;

            // Remove dependencies of which the dependency is no longer in the nodeColumn.
            foreach ($this->dependencies as $ix => [$node, $dependency]) {
                if (in_array($dependency, $nodeColumn) === false) {
                    unset($this->dependencies[$ix]);
                }
            }

            // Make sure at least something has been removed.
            if ($count === count($this->dependencies)) throw new Exception("Sort failed");
        }

        return $sorted;
    }
}
