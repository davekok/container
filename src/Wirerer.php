<?php

declare(strict_types=1);

namespace davekok\wiring;

use Throwable;

/**
 * The wirerer. Wires all the wireables.
 *
 * Also see the run script: bin/run
 */
class Wirerer implements Wireable
{
    private readonly Component $component;
    private readonly Wirings $wirings;
    private readonly array $runnables;

    public function __construct(
        private readonly string $appdir,
        private readonly array $args,
        private readonly Parser $parser = new Parser,
    ) {}

    public function wire(): Runnable
    {
        try {
            $this->component = new Component("root", $this->appdir);
            $this->wirings   = (new WiringsFactory($this->component))->createWirings();

            $this->scanConfig($this->component->loadConfig());

            foreach ($this->wirings->all() as $wirings) {
                foreach ($wirings as $wiring) {
                    $wiring->prewire();
                }
            }

            $args = $this->scanArguments($args);

            foreach ($this->wirings->all() as $wirings) {
                foreach ($wirings as $wiring) {
                    $wiring->wire();
                }
            }

            return $this->runnable($args);
        } catch (Throwable $e) {
            $this->printThrowable($e);
        }
    }

    private function scanConfig(array $config): void
    {
        if (isset($config["davekok/wiring"]["runnables"])) {
            $this->runnables = $config["davekok/wiring"]["runnables"];
        } else if (isset($config["davekok/wiring.runnables"])) {
            $this->runnables = $config["davekok/wiring.runnables"];
        } else if (isset($config["runnables"])) {
            $this->runnables = $config["runnables"];
        }
        unset($config["davekok/wiring"], $config["davekok/system.wiring"], $config["runnables"]);

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                ["vendor" => $vendor, "component" => $component] = $this->parser->parseComponent($key);
                foreach ($value as $parameter => $paremeterValue) {
                    $this->wirings->setParameter($vendor, $component, $parameter, $paremeterValue);
                }
                continue;
            }
            ["vendor" => $vendor, "component" => $component, "parameter" => $parameter] = $this->parser->parseParameter($key);
            $this->wirings->setParameter($vendor, $component, $parameter, $value);
        }
    }

    private function scanArguments(array $args): array
    {
        $offset = 0;
        $length = count($args);
        if ($length === 0) return $args;
        if ($args[$offset] === "--help") $this->printHelp();

        try {
            while ($offset < $length) {
                $this->wirings->setParameter(...$this->parseArgument($args[$i]));
            }
        } catch (ParserException $e) {
            // on error return remaining arguments for runnable
            return array_slice($args, $i);
        }
    }

    private function runnable(array $args): Runnable
    {
        $unparsedRunnable = array_shift($args) ?? $this->runnables["default"] ?? throw new WiringException("No default runnable.");
        if (is_array($unparsedRunnable)) {
            $runnableArgs     = $unparsedRunnable;
            $unparsedRunnable = array_shift($runnableArgs);
        } else {
            $runnableArgs = [];
        }
        $key = $this->runnables[$unparsedRunnable] ?? $unparsedRunnable;

        switch ($key) {
            case "help":
                switch (count($args)) {
                    case 0:
                        $this->printHelp();
                    case 1:
                        ["vendor" => $vendor, "component" => $component, "runnable" => $runnable]
                            = $this->parser->parseRunnable($key);
                        $this->printnl($this->wirings->helpRunnable($vendor, $component, $runnable));
                        exit();
                    default:
                        foreach ($args as $arg) {
                            ["vendor" => $vendor, "component" => $component, "runnable" => $runnable]
                                = $this->parser->parseRunnable($key);
                            $this->printnl($unparsedRunnable);
                            $this->printnlnl($this->wirings->helpRunnable($vendor, $component, $runnable));
                        }
                        exit();
                }

            case "list":
                $this->printnl($this->formatListRunnables());
                exit();

            default:
                ["vendor" => $vendor, "component" => $component, "runnable" => $runnable]
                    = $this->parser->parseRunnable($key);

                if ($args === ["help"]) {
                    $this->printnlnl($this->wirings->helpRunnable($vendor, $component, $runnable));
                    exit();
                }

                return $this->wirings->runnable($vendor, $component, $runnable, [...$runnableArgs, ...$args]);
        }
    }

    private function printHelp(): never
    {
        $this->printnlnl($this->formatInfo());
        $this->printnlnl($this->formatListRunnables());
        $this->printnlnl($this->formatListParameters());
        exit();
    }

    private function printThrowable(Throwable $throwable): never
    {
        exit("{$throwable->getMessage()}\n"
            . "## {$throwable->getFile()}({$throwable->getLine()}): " . get_class($throwable) . "\n"
            . $throwable->getTraceAsString()
            . "\n");
    }

    private function formatInfo(): string
    {
        $info = $this->component->getInfo();
        $text = $info["name"] . "\n";
        if (isset($info["description"])) {
            $text .= $info["description"] . "\n";
        }
        return $text;
    }

    private function formatListRunnables(): string
    {
        $text = "List of available runnables:\n";
        $runnables = array_flip($this->runnables);
        foreach ($this->wirings->all() as $vendor => $components) {
            foreach ($components as $component => $wiring) {
                foreach ($wiring->listRunnables() as $runnable) {
                    $key = "$vendor/$component::$runnable";
                    $text .= $runnables[$key] ?? $key;
                    $text .= "\n";
                }
            }
        }
        return $text;
    }

    private function formatListParameters(): string
    {
        $text = "List of available parameters:\n";
        foreach ($this->wirings->all() as $component => $wiring) {
            foreach ($wiring->infoParameters() as $parameter => $help) {
                $text .= "--$component.$parameter\n";
                $text .= "    $help\n";
                $value = $wiring->getParameter($parameter);
                if ($value !== null) {
                    $text .= "    Value: $value\n";
                } else {
                    $text .= "    No value\n";
                }
            }
        }
        return $text;
    }

    private function printnl(string $text): void
    {
        print $text;

        if (str_ends_with($text, "\n")) {
            return;
        }

        print "\n";
    }

    private function printnlnl(string $text): void
    {
        print $text;

        if (str_ends_with($text, "\n\n")) {
            return;
        }

        if (str_ends_with($text, "\n")) {
            print "\n";
            return;
        }

        print "\n\n";
    }
}
