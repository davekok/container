<?php

declare(strict_types=1);

namespace davekok\system;

use Exception;
use Throwable;

class Printer
{
    public function __construct() {}

    public function printHelp(array $info): never
    {
        echo "List of available parameters:\n";
        $this->printInfo($info);
        exit();
    }

    public function printThrowable(Throwable $throwable): never
    {
        exit("{$throwable->getMessage()}\n"
            . "## {$throwable->getFile()}({$throwable->getLine()}): " . get_class($throwable) . "\n"
            . $throwable->getTraceAsString()
            . "\n");
    }

    private function printInfo(array $info, string $prefix = ""): void
    {
        foreach ($info as $key => $value) {
            if (is_array($value)) {
                $this->printInfo($info, "$prefix$key.");
                continue;
            }
            echo "--$prefix$key\n    $value\n";
        }
    }
}
