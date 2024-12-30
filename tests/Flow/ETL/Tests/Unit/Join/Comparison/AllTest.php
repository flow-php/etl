<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Join\Comparison;

use function Flow\ETL\DSL\int_entry;
use Flow\ETL\Join\Comparison;
use Flow\ETL\Join\Comparison\All;
use Flow\ETL\Row;
use Flow\ETL\Tests\FlowTestCase;

final class AllTest extends FlowTestCase
{
    public function test_failure() : void
    {
        $comparison1 = self::createStub(Comparison::class);
        $comparison1
            ->method('compare')
            ->willReturn(true);

        $comparison2 = self::createStub(Comparison::class);
        $comparison2
            ->method('compare')
            ->willReturn(false);

        self::assertFalse(
            (new All($comparison1, $comparison2))
                ->compare(
                    Row::create(int_entry('id', 1)),
                    Row::create(int_entry('id', 2)),
                )
        );
    }

    public function test_success() : void
    {
        $comparison1 = self::createStub(Comparison::class);
        $comparison1
            ->method('compare')
            ->willReturn(true);

        $comparison2 = self::createStub(Comparison::class);
        $comparison2
            ->method('compare')
            ->willReturn(true);

        self::assertTrue(
            (new All($comparison1, $comparison2))
                ->compare(
                    Row::create(int_entry('id', 1)),
                    Row::create(int_entry('id', 2)),
                )
        );
    }
}
