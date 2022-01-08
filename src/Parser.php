<?php

declare(strict_types=1);

namespace davekok\wiring;

class Parser
{
    private readonly string $argumentPattern;
    private readonly string $parameterPattern;
    private readonly string $runnablePattern;
    private readonly string $componentPattern;

    public function __construct()
    {
        $name          = '[A-Za-z0-9-]+';
        $vendor        = $name;
        $component     = $name;
        $parameter     = $name;
        $runnable      = $name;
        $simplevalue   = '[\x20\x23-\x26\x28-\x7E]*';
        $squotedvalue  = '\'(?:[\x20-\x26\x28-\x5B\x5D-\x7E]|\\\\\'|\\\\\\\\|\\\\r|\\\\n|\\\\t)*\'';
        $dquotedvalue  = '"(?:[\x20\x23-\x5B\x5D-\x7E]|\\\\"|\\\\\\\\|\\\\r|\\\\n|\\\\t)*"';
        $value         = "(?:$simplevalue|$squotedvalue|$dquotedvalue)";

        $this->argumentPattern  = "/^--($vendor)\\/($component)\\.($parameter)(=$value)?$/";
        $this->parameterPattern = "/^($vendor)\\/($component)\\.($parameter)$/";
        $this->runnablePattern  = "/^($vendor)\\/($component)#($runnable)$/";
        $this->componentPattern = "/^($vendor)\\/($component)$/";
    }

    public function parseParameter(string $parameter): ?array
    {
        return preg_match($this->parameterPattern, $parameter, $matches) === 1
            ? [
                "vendor"     => $matches[1],
                "component"  => $matches[2],
                "parameter"  => $matches[3],
            ]
            : throw new ParserException("Not a parameter: $parameter");
    }

    public function parseArgument(string $arg): ?array
    {
        return preg_match($this->argumentPattern, $arg, $matches) === 1
            ? [
                "vendor"     => $matches[1],
                "component"  => $matches[2],
                "parameter"  => $matches[3],
                "value"      => match ($matches[4] ?? "") {
                    ""       => true,
                    "="      => null,
                    "=true"  => true,
                    "=false" => false,
                    default  => $this->convert(substr($matches[4], 1)),
                },
            ]
            : throw new ParserException($arg);
    }

    public function parseRunnable(string $runnable): ?array
    {
        return preg_match($this->runnablePattern, $runnable, $matches) === 1
            ? [
                "vendor"     => $matches[1],
                "component"  => $matches[2],
                "runnable"   => $matches[3],
            ]
            : throw new ParserException("Not a runnable: $runnable");
    }

    public function parseComponent(string $component): ?array
    {
        return preg_match($this->componentPattern, $component, $matches) === 1
            ? [
                "vendor"     => $matches[1],
                "component"  => $matches[2],
            ]
            : throw new ParserException("Not a component: $component");
    }

    private function convert(string $value): int|float|string
    {
        return match (true) {
            $value[0] === "'" || $value[0] === '"'              => stripcslashes(substr($value, 1, -1)),
            filter_var($value, FILTER_VALIDATE_INT) !== false   => (int)$value,
            filter_var($value, FILTER_VALIDATE_FLOAT) !== false => (float)$value,
            default                                             => $value,
        };
    }
}
