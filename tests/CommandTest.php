<?php

declare(strict_types=1);

namespace davekok\container\tests;

use davekok\container\Command;
use davekok\container\ContainerFactory;
use davekok\container\Runnable;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversDefaultClass \davekok\container\Command
 * @covers ::__construct
 */
class CommandTest extends TestCase
{
    /**
     * @covers ::set
     */
    public function testSet(): void
    {
        $factory = $this->createMock(ContainerFactory::class);
        $factory->expects(static::once())->method("set");
        $command = new Command($factory);
        $command->set("key", "value");
    }

    // public function testSetThrows(): void
    // {
    //     $factory = $this->createMock(ContainerFactory::class);
    //     $factory->expects(static::once())->method("set")->willThrowException(new Exception("sef"));
    //     $command = new Command($factory);
    //     $command->set("key", "value");
    // }

    /**
     * @covers ::getLongestKeyLength
     * @covers ::printHelp
     * @covers ::printInfo
     */
    public function testHelp(): void
    {
        $this->expectOutputString("List of options:\n  --key   value\n  --key1  value\n  --key11 value\n");
        $factory = $this->createMock(ContainerFactory::class);
        $factory->expects(static::once())->method("info")->willReturn([
            "key" => "value",
            "key1" => "value",
            "key11" => "value",
        ]);
        $command = new Command($factory);
        $command->printHelp();
    }

    /**
     * @covers ::main
     */
    public function testMain(): void
    {
        $main = new class implements Runnable { public function run():never{exit();}};
        $container = new stdClass;
        $container->main = $main;
        $factory = $this->createMock(ContainerFactory::class);
        $factory->expects(static::once())->method("set")->with("key", "value");
        $factory->expects(static::once())->method("createContainer")->willReturn($container);
        $command = new Command($factory);
        static::assertSame($main, $command->main(["--key", "value"]));
    }
}
