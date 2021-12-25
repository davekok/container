<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class Component
{
    private readonly Component $root;

    public function __construct(
        public readonly string $name,
        public readonly string $directory,
        Component|null $root = null,
    ) {
        $this->root = $root ?? $this;
    }

    public function createWiring(): Wiring|null
    {
        $class = "\\" . str_replace(".", "\\", $this->name) . "\\Wiring";
        return class_exists($class) ? new $class : null;
    }

    public function getInfo(): array
    {
        $composer = $this->loadComposer();
        return [
            "name"        => $composer->name ?? $this->name,
            "description" => $composer->description ?? null,
        ];
    }

    public function getDependencies(): array
    {
        $dependencies = [];
        $composer = $this->loadComposer();
        foreach ([$composer['require'] ?? [], $composer['require-dev'] ?? []] as $require) {
            foreach (array_keys($require) as $dependency) {
                $name = str_replace("/", ".", $dependency);
                $dependencies[$name] = new Component($name, $this->root->directory . "/vendor/" . $dependency, $this->root);
            }
        }
        return $dependencies;
    }

    public function loadConfig(): array
    {
        return $this->loadJson("{$this->directory}/config.json");
    }

    public function loadComposer(): array
    {
        return $this->loadJson("{$this->directory}/composer.json");
    }

    private function loadJson(string $file): array
    {
        return file_exists($file)
            ? json_decode(
                json:        file_get_contents($file) ?: throw new Exception("Unable to read file: {$file}"),
                associative: true,
                flags:       JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
            )
            : [];
    }
}
