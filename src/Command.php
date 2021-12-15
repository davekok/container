<?php

declare(strict_types=1);

namespace davekok\container;

use Throwable;

class Command
{
    public function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly int $tabWidth = 2,
        private readonly int $lineWidth = 80,
    ) {}

    public function set(string $key, string $value): self
    {
        try {
            $this->containerFactory->set($key, $value);
            return $this;
        } catch (Throwable $throwable) {
            $this->printError($throwable);
        }
    }

    public function main(array $args): Runnable
    {
        $count = count($args);
        if ($count === 0) {
            return $this->containerFactory->createContainer()->main;
        }

        if ($args[0] === "--help") {
            $this->printHelp();
        }

        for ($i = 0; $i < $count; ++$i) {
            $key = $args[$i];
            if ($key[0] !== "-" && $key[1] !== "-") {
                exit("Invalid option $key\n");
            }
            $key = substr($key, 2);
            $value = $args[$i + 1];
            if ($value[0] === "-" && $value[1] === "-") {
                $this->containerFactory->set($key, true);
                continue;
            }
            ++$i;
            if ($value === "true") $value = true;
            else if ($value === "false") $value = false;
            else if ($value === "null") $value = null;
            else if ($value[0] === "'" && $value[strlen($value)-1] === "'") $value = stripcslashes(substr($value, 0, -1));
            else if ($value[0] === '"' && $value[strlen($value)-1] === '"') $value = stripcslashes(substr($value, 0, -1));
            else if (filter_var($value, FILTER_VALIDATE_INT) !== false) $value = (int)$value;
            else if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) $value = (float)$value;
            $this->containerFactory->set($key, $value);
        }

        return $this->containerFactory->createContainer()->main;
    }

    public function printHelp(): void
    {
        $info = $this->containerFactory->info();
        $longestKeyLength = $this->getLongestKeyLength($info);

        echo "List of options:\n";
        $this->printInfo($info, $longestKeyLength);
    }

    private function getLongestKeyLength(array $info, string $prefix = "", int $current = 0): int
    {
        foreach ($info as $key => $value) {
            $length = strlen($prefix) + strlen($key);
            if ($length > $current) $current = $length;
            if (is_array($value)) {
                $length = $this->getLongestKeyLength($value, "$prefix$key.", $current);
                if ($length > $current) $current = $length;
            }
        }

        return $current;
    }

    private function printInfo(array $info, int $longestKeyLength, string $prefix = ""): void
    {
        foreach ($info as $key => $value) {
            if (is_array($value)) {
                $this->printInfo($info, $longestKeyLength, "$prefix$key.");
                continue;
            }
            $option = str_repeat(" ", $this->tabWidth)
                . "--$prefix$key"
                . str_repeat(" ", $longestKeyLength - strlen($key));
            $option .= str_repeat(" ", $this->tabWidth - (strlen($option) % $this->tabWidth));
            $option .= $value;
            echo wordwrap($option, $this->lineWidth) . "\n";
        }
    }

    private function printError(Throwable $throwable): never
    {
        exit("{$throwable->getMessage()}\n"
            . "## {$throwable->getFile()}({$throwable->getLine()}): " . get_class($throwable) . "\n"
            . $throwable->getTraceAsString()
            . "\n");
    }
}
