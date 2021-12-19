<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class WiringsFactory
{
    private array $dependencies = [];
    private array $components = [];

    public function __construct(
        private readonly string $pkgdir,
    ) {}

    public function createWirings(): Wirings
    {
        $composer = $this->loadComposer($this->pkgdir);

        foreach ([$composer['require'] ?? [], $composer['require-dev'] ?? []] as $section) {
            foreach (array_keys($section) as $component) {
                $this->scanComponent(str_replace("/", ".", $component));
            }
        }

        // Remove dependencies for which there is no wiring.
        foreach ($this->dependencies as $ix => [$component, $dependency]) {
            if (isset($this->components[$dependency]) === false) {
                unset($this->dependencies[$ix]);
            }
        }

        return new Wirings(self::topsort($this->components, $this->dependencies));
    }

    private function scanComponent(string $component): void
    {
        $class = "\\" . str_replace(".", "\\", $component) . "\\Wiring";
        if (class_exists($class) === false) {
            return;
        }

        $this->components[$component] = new $class;

        $path = $this->pkgdir . "/vendor/" . str_replace(".", "/", $component) . "/composer.json";
        if (file_exists($path)) {
            $composer = $this->loadComposer($path);
            foreach ([$composer['require'] ?? [], $composer['require-dev'] ?? []] as $section) {
                foreach (array_keys($section) as $dependency) {
                    $this->dependencies[] = [$component, $dependency];
                }
            }
        }
    }

    private function loadComposer(string $dir): array
    {
        return json_decode(
            json:        file_get_contents("$dir/composer.json") ?: throw new Exception("Unable to read file: $dir/composer.json"),
            associative: true,
            flags:       JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
        );
    }

    public static function topsort(array $nodes, array $dependencies): array
    {
        $sorted = [];

        while (count($nodes) > 0) {
            $nodeColumn = array_column($dependencies, 0);

            $count = count($nodes);
            foreach ($nodes as $node => $value) {
                if (in_array($node, $nodeColumn) === false) {
                    $sorted[$node] = $value;
                    unset($nodes[$node]);
                }
            }
            if ($count === count($nodes)) throw new Exception("Sort failed");

            $count = count($dependencies);
            if ($count === 0) continue;
            foreach ($dependencies as $ix => [$node, $dependency]) {
                if (in_array($dependency, $nodeColumn) === false) {
                    unset($dependencies[$ix]);
                }
            }
            if ($count === count($dependencies)) throw new Exception("Sort failed");
        }

        return $sorted;
    }
}
