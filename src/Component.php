<?php

declare(strict_types=1);

namespace davekok\wiring;

use Exception;
use ParseError;

class Component
{
    private readonly Component $root;
    private readonly array $composer;
    private readonly array $parameters;

    public function __construct(
        public readonly string $name,
        public readonly string $directory,
        Component|null $root = null,
    ) {
        $this->root = $root ?? $this;
    }

    public function createWiring(): Wiring|null
    {
        return $this->scanDirsForWiring($this->directory);
    }

    public function getInfo(): array
    {
        $composer = $this->getComposer();
        return [
            "name"        => $composer["name"] ?? $this->name,
            "description" => $composer["description"] ?? null,
        ];
    }

    public function getDependencies(): array
    {
        $dependencies = [];
        $composer = $this->getComposer();
        foreach ([$composer['require'] ?? [], $composer['require-dev'] ?? []] as $require) {
            foreach (array_keys($require) as $dependency) {
                $dependencies[$dependency] = new Component($dependency, $this->root->directory . "/vendor/" . $dependency, $this->root);
            }
        }
        return $dependencies;
    }

    public function getParameters(): array
    {
        return $this->parameters ??= $this->loadJson("{$this->directory}/parameters.json");
    }

    public function getComposer(): array
    {
        return $this->composer ??= $this->loadJson("{$this->directory}/composer.json");
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

    private function scanDirsForWiring(string ...$dirs): Wiring|null
    {
        $queue = [];
        foreach ($dirs as $dir) {
            if (is_dir($dir) === false) continue;
            $dirres = opendir($dir);
            if ($dirres === false) continue;

            while (($entry = readdir($dirres)) !== false) {
                if ($entry[0] === ".") {
                    continue;
                }

                $file = "$dir/$entry";

                if (is_dir($file) === true) {
                    $queue[] = $file;
                    continue;
                }

                if (is_file($file) === false) {
                    continue;
                }

                if (str_ends_with($file, "Wiring.php") === false) {
                    continue;
                }

                $className = $this->getClassFromSource($file);
                if ($className === null) {
                    continue;
                }

                if (isset(class_implements($className)['davekok\wiring\Wiring'])) {
                    closedir($dirres);
                    return new $className;
                }
            }
            closedir($dirres);
        }

        if (count($queue) > 0) {
            return $this->scanDirsForWiring(...$queue);
        }

        return null;
    }

    private function getClassFromSource(string $file): string|null
    {
        $namespace = "";
        $state     = 0;
        try {
            foreach (token_get_all(file_get_contents($file), TOKEN_PARSE) as [$token, $text, $line]) {
                if ($token === null) {
                    continue;
                }
                switch ($state) {
                    case 0; // find namespace or class
                        switch ($token) {
                            case T_NAMESPACE:
                                $state = 1;
                                continue 3;
                            case T_CLASS:
                                $state = 2;
                                continue 3;
                            default:
                                continue 3;
                        }

                    case 1:
                        switch ($token) {
                            case T_WHITESPACE: // skip white space
                                continue 3;
                            case T_NAME_QUALIFIED:
                                $namespace = $text;
                                $state = 0;
                                continue 3;
                        }

                    case 2:
                        switch ($token) {
                            case T_WHITESPACE: // skip white space
                                continue 3;
                            case T_STRING:
                                return "$namespace\\$text";
                        }
                }
            }
            return null;
        } catch (ParseError $error) {
            $relativePath = substr($file, strlen($this->directory) + 1);
            exit("{$this->name}:{$relativePath}:{$error->getLine()}: {$error->getMessage()}\n");
        }
    }
}
