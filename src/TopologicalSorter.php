<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class TopologicalSorter
{
    /**
     * Map of nodes.
     */
    private array /* {[key: string|int]: mixed;}  */ $nodes = [];

    /**
     * Two-dimensional array, column one is the key of node, column two is the key of the node on which it depends.
     */
    private array /* [string|int, string|int][] */ $dependencies = [];

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
     *
     * Order is nodes without dependencies first (trunk), nodes on which nothing depends last (leafs).
     *
     * Declared dependencies that are not in the node map are removed.
     */
    public function sort(): array
    {
        $sorted = [];

        // Remove dependencies without corresponding entry in node map.
        foreach ($this->dependencies as $ix => [$node, $dependency]) {
            if (isset($this->nodes[$node]) === false || isset($this->nodes[$dependency]) === false) {
                unset($this->dependencies[$ix]);
            }
        }

        while (count($this->nodes) > 0) {

            // Extract the node column from two-dimensional dependencies array.
            $nodeColumn = array_column($this->dependencies, 0);

            // Remember count so we can check progress is made later on.
            $count = count($this->nodes);

            // Add nodes to sorted that are no longer in nodeColumn.
            foreach ($this->nodes as $key => $value) {
                if (in_array($key, $nodeColumn) === false) {
                    $sorted[$key] = $value;
                    unset($this->nodes[$key]);
                }
            }

            // Make sure at least something has been removed and thus there is progress.
            if ($count === count($this->nodes)) throw new Exception("Sort failed");

            // Remember count so we can check progress is made later on.
            $count = count($this->dependencies);

            // However, if there are no more dependencies just add remaining node and return.
            if ($count === 0) {
                return [...$sorted, ...$this->nodes];
            }

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
