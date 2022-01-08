<?php

declare(strict_types=1);

namespace davekok\wiring\tests;

use davekok\wiring\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \davekok\wiring\Parser
 * @covers ::__construct
 */
class ParserTest extends TestCase
{
    /**
     * @covers ::parseParameter
     */
    public function testParseParameter(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
            ],
            (new Parser)->parseParameter("davekok/kernel.log")
        );
    }

    /**
     * @covers ::parseRunnable
     */
    public function testParseRunnable(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "runnable"  => "kernel",
            ],
            (new Parser)->parseRunnable("davekok/kernel#kernel")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentSimpleValue(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "pipe://stderr",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=pipe://stderr")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentIntegerValue(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => 2,
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=2")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentFloatValue(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => 2.1,
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=2.1")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentSingleQuotedValue(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "pipe://stderr",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log='pipe://stderr'")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentDoubleQuotedValue(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "pipe://stderr",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=\"pipe://stderr\"")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseBooleanArgument(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => true,
            ],
            (new Parser)->parseArgument("--davekok/kernel.log")
        );
    }

    /**
     * @covers ::parseArgument
     */
    public function testParseArgumentEmpty(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => null,
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentSingleQuotesEmpty(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=''")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentDoubleQuotesEmpty(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=\"\"")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentSingleQuotesWithEscapedChars(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "\\ ' \t \r \n",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log='\\\\ \\' \\t \\r \\n'")
        );
    }

    /**
     * @covers ::parseArgument
     * @covers ::convert
     */
    public function testParseArgumentDoubleQuotesWithEscapedChars(): void
    {
        self::assertEquals(
            [
                "vendor"    => "davekok",
                "component" => "kernel",
                "parameter" => "log",
                "value"     => "\\ \" \t \r \n",
            ],
            (new Parser)->parseArgument("--davekok/kernel.log=\"\\\\ \\\" \\t \\r \\n\"")
        );
    }
}
