<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

/**
 * Class to bring the system online.
 */
class System
{
    private readonly Wirings $wirings;

    public function __construct(private readonly string $pkgdir) {}

    public function boot(array $args): Runnable
    {
        try {
            $this->wirings = (new WiringsFactory($this->pkgdir))->createWirings();

            $this->scanArgs($args);

            if (file_exists("$this->pkgdir/config.json")) {
                $this->scanConfig(json_decode(
                    json:        file_get_contents("$this->pkgdir/config.json") ?: throw new Exception("Unable to load file config.json"),
                    associative: true,
                    flags:       JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
                ));
            }

            $runnable = null;
            foreach ($this->wirings->getAll() as $wiring) {
                $value = $wiring->wire($this->wirings);
                if ($value instanceof Runnable) {
                    $runnable = $value;
                }
            }
            $runnable instanceof Runnable ?: throw new Exception("Nothing to run.");
        } catch (Throwable $e) {
            (new Printer)->printThrowable($e);
        }
        return $runnable;
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
            (new Printer)->printHelp($this->wirings->infoParameters());
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
}
