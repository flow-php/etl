<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Function;

use function Flow\ETL\DSL\{array_exists, from_array, ref, to_memory};
use Flow\ETL\Flow;
use Flow\ETL\Memory\ArrayMemory;
use PHPUnit\Framework\TestCase;

final class ArrayPathExistsTest extends TestCase
{
    public function test_array_path_exists() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['id' => 1, 'array' => ['a' => 1, 'b' => 2, 'c' => 3]],
                        ['id' => 2],
                    ]
                )
            )
            ->withEntry('has_array', ref('array')->arrayPathExists('a'))
            ->drop('array')
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['id' => 1, 'has_array' => true],
                ['id' => 2, 'has_array' => false],
            ],
            $memory->dump()
        );
    }

    public function test_array_path_function_exists() : void
    {
        (new Flow())
            ->read(
                from_array(
                    [
                        ['id' => 1, 'array' => ['a' => 1, 'b' => 2, 'c' => 3]],
                        ['id' => 2],
                    ]
                )
            )
            ->withEntry('has_array', array_exists(ref('array'), 'a'))
            ->drop('array')
            ->write(to_memory($memory = new ArrayMemory()))
            ->run();

        self::assertSame(
            [
                ['id' => 1, 'has_array' => true],
                ['id' => 2, 'has_array' => false],
            ],
            $memory->dump()
        );
    }
}
