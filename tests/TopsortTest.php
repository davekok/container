<?php

declare(strict_types=1);

namespace davekok\system\tests;

use davekok\system\WiringsFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \davekok\system\WiringsFactory
 */
class TopsortTest extends TestCase
{
    /**
     * @covers ::topsort
     */
    public function testTopsort(): void
    {
        static::assertSame(
            [
                "a" => "1",
                "b" => "2",
                "c" => "3",
                "d" => "4",
            ],
            WiringsFactory::topsort(
                [
                    "d" => "4",
                    "c" => "3",
                    "b" => "2",
                    "a" => "1",
                ],
                [
                    ["b", "a"],
                    ["c", "b"],
                    ["d", "c"],
                ],
            )
        );
    }

    /**
     * @covers ::topsort
     */
    public function testTopsort2(): void
    {
        static::assertSame(
            [
                "a" => "1",
                "c" => "3",
                "b" => "2",
                "d" => "4",
            ],
            WiringsFactory::topsort(
                [
                    "d" => "4",
                    "c" => "3",
                    "b" => "2",
                    "a" => "1",
                ],
                [
                    ["b", "a"],
                    ["c", "a"],
                    ["d", "c"],
                ],
            )
        );
    }

    /**
     * @covers ::topsort
     */
    public function testTopsort3(): void
    {
        static::assertSame(
            [
                "c" => "3",
                "a" => "1",
                "d" => "4",
                "b" => "2",
            ],
            WiringsFactory::topsort(
                [
                    "d" => "4",
                    "c" => "3",
                    "b" => "2",
                    "a" => "1",
                ],
                [
                    ["b", "a"],
                    ["b", "c"],
                    ["d", "c"],
                ],
            )
        );
    }
}
