<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class Wirings
{
    public function __construct(
        private readonly array $wirings = [],
    ) {}

    public function get(string $key): Wiring
    {
        return $this->wirings[$key] ?? throw new WiringException("Not found: $key");
    }

    public function getAll(): array
    {
        return $this->wirings;
    }

    public function infoParameters(): array
    {
        $info = [];
        foreach ($wirings as $key => $wiring) {
            $info[$key] = $wiring->infoParameters();
        }
        return $info;
    }

    public function setParameter(string $key, string|int|float|bool $value): self
    {
        try {
            $this->findWiringByKey($key, $subkey)->set($subkey, $value);
            return $this;
        } catch (Throwable $throwable) {
            $this->printError($throwable);
        }
    }

    public function getParameter(string $key): string|int|float|bool
    {
        return $this->findWiringByKey($key, $subkey)->get($subkey);
    }

    private function findWiringByKey(string $key, string &$subkey): Wiring
    {
        $mainkey = strtok($key, ".");
        strlen($mainkey) === strlen($key) ?: throw new WiringException("Invalid key: $key");
        $wiring = $this->wirings[$mainkey] ?? throw new WiringException("Not found: $mainkey");
        $subkey = substr($key, 1 + strlen($mainkey));
        return $wiring;
    }
}
