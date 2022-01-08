<?php

declare(strict_types=1);

namespace davekok\wiring\tests;

use davekok\wiring\TopologicalDependencySorter;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \davekok\system\TopologicalDependencySorter
 * @covers ::addNode
 * @covers ::addDependency
 * @covers ::sort
 */
class TopologicalDependencySorterTest extends TestCase
{
    public function testTopsort(): void
    {
        $sorter = new TopologicalDependencySorter;
        $sorter->addNode("d", "4");
        $sorter->addNode("c", "3");
        $sorter->addNode("b", "2");
        $sorter->addNode("a", "1");
        $sorter->addDependency("b", "a");
        $sorter->addDependency("c", "b");
        $sorter->addDependency("d", "c");
        static::assertSame(
            [
                "a" => "1",
                "b" => "2",
                "c" => "3",
                "d" => "4",
            ],
            $sorter->sort()
        );
    }

    public function testTopsort2(): void
    {
        $sorter = new TopologicalDependencySorter;
        $sorter->addNode("d", "4");
        $sorter->addNode("c", "3");
        $sorter->addNode("b", "2");
        $sorter->addNode("a", "1");
        $sorter->addDependency("b", "a");
        $sorter->addDependency("c", "a");
        $sorter->addDependency("d", "c");
        static::assertSame(
            [
                "a" => "1",
                "c" => "3",
                "b" => "2",
                "d" => "4",
            ],
            $sorter->sort()
        );
    }

    public function testTopsort3(): void
    {
        $sorter = new TopologicalDependencySorter;
        $sorter->addNode("d", "4");
        $sorter->addNode("c", "3");
        $sorter->addNode("b", "2");
        $sorter->addNode("a", "1");
        $sorter->addDependency("b", "a");
        $sorter->addDependency("b", "c");
        $sorter->addDependency("d", "c");
        static::assertSame(
            [
                "c" => "3",
                "a" => "1",
                "d" => "4",
                "b" => "2",
            ],
            $sorter->sort()
        );
    }
}
