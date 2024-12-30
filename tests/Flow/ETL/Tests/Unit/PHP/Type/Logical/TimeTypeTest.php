<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\PHP\Type\Logical;

use function Flow\ETL\DSL\{type_int, type_time};
use Flow\ETL\Tests\FlowTestCase;

final class TimeTypeTest extends FlowTestCase
{
    public function test_equals() : void
    {
        self::assertTrue(
            type_time()->isEqual(type_time())
        );
        self::assertFalse(
            type_time()->isEqual(type_int())
        );
    }

    public function test_is_valid() : void
    {
        self::assertTrue(type_time(true)->isValid(null));
        self::assertTrue(type_time()->isValid(new \DateInterval('PT10S')));
        self::assertFalse(type_time()->isValid('00:00:01'));
        self::assertFalse(type_time()->isValid('PT10S'));
    }

    public function test_merge_non_nullable_with_non_nullable() : void
    {
        self::assertFalse(type_time()->merge(type_time())->nullable());
    }

    public function test_merge_non_nullable_with_nullable() : void
    {
        self::assertTrue(type_time()->merge(type_time(true))->nullable());
        self::assertTrue(type_time(true)->merge(type_time(false))->nullable());
    }

    public function test_merge_nullable_with_nullable() : void
    {
        self::assertTrue(type_time(true)->merge(type_time(true))->nullable());
    }

    public function test_to_string() : void
    {
        self::assertSame(
            'time',
            type_time()->toString()
        );
        self::assertSame(
            '?time',
            type_time(true)->toString()
        );
    }
}
