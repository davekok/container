<?php

declare(strict_types=1);

namespace davekok\system;

use Throwable;

/**
 * Class to bring the system online.
 *
 * See also the system script in bin.
 */
class System
{
    private readonly Component $component;
    private readonly Wirings $wirings;

    public function __construct(private readonly string $prjdir) {}

    public function boot(array $args): Runnable
    {
        try {
            $this->component = new Component("main", $this->prjdir);
            $this->wirings   = (new WiringsFactory($this->component))->createWirings();
            $this->scanArgs($args);
            $this->scanConfig($this->component->loadConfig());
            $this->prewire();
            return $this->wire();
        } catch (Throwable $e) {
            $this->printThrowable($e);
        }
    }

    private function wire(): Runnable
    {
        $runnable = null;
        foreach ($this->wirings->getAll() as $wiring) {
            $value = $wiring->wire($this->wirings);
            if ($value instanceof Runnable) {
                $runnable = $value;
            }
        }
        return $runnable ?? throw new WiringException("Nothing to run.");
    }

    private function prewire(): void
    {
        foreach ($this->wirings->getAll() as $wiring) {
            $wiring->prewire($this->wirings);
        }
    }

    private function scanConfig(array $config, string $prefix = ""): void
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->scanConfig($value, "$prefix$key.");
                continue;
            }
            $this->wirings->setParameter("$prefix$key", $value);
        }
    }

    private function scanArgs(array $args): void
    {
        $count = count($args);
        if ($count === 0) {
            return;
        }

        if ($args[0] === "--help") {
            $this->printHelp();
        }

        for ($i = 0; $i < $count; ++$i) {
            $key = $args[$i];
            if ($key[0] !== "-" && $key[1] !== "-") {
                exit("Invalid option $key\n");
            }
            $key   = substr($key, 2);
            $value = $args[$i + 1];
            if ($value[0] === "-" && $value[1] === "-") {
                $this->wirings->setParameter($key, true);
                continue;
            }
            ++$i;

            $this->wirings->setParameter($key, match (true) {
                $value === "true"                                     => true,
                $value === "false"                                    => false,
                $value === "null"                                     => null,
                $value[0] === "'" && $value[strlen($value)-1] === "'" => stripcslashes(substr($value, 0, -1)),
                $value[0] === '"' && $value[strlen($value)-1] === '"' => stripcslashes(substr($value, 0, -1)),
                filter_var($value, FILTER_VALIDATE_INT) !== false     => (int)$value,
                filter_var($value, FILTER_VALIDATE_FLOAT) !== false   => (float)$value,
                default                                               => $value,
            });
        }
    }

    public function printHelp(): never
    {
        $info = $this->component->getInfo();
        echo $info["name"] . "\n";
        if (isset($info["description"])) {
            echo $info["description"] . "\n";
        }
        echo "\n";

        echo "List of available parameters:\n";
        $this->printParameters($this->wirings->infoParameters());
        exit();
    }

    public function printThrowable(Throwable $throwable): never
    {
        exit("{$throwable->getMessage()}\n"
            . "## {$throwable->getFile()}({$throwable->getLine()}): " . get_class($throwable) . "\n"
            . $throwable->getTraceAsString()
            . "\n");
    }

    private function printParameters(array $parameters, string $prefix = ""): void
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $this->printParameters($parameters, "$prefix$key.");
                continue;
            }
            echo "--$prefix$key\n    $value\n";
        }
    }
}
