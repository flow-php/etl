<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use function Flow\ETL\DSL\{int_entry, json_entry, lit, not, ref};
use Flow\ETL\Row;
use Flow\ETL\Tests\FlowTestCase;

final class NotTest extends FlowTestCase
{
    public function test_not_expression_on_array_true_value() : void
    {
        self::assertFalse(
            not(lit([1, 2, 3]))->eval(Row::create())
        );
    }

    public function test_not_expression_on_boolean_true_value() : void
    {
        self::assertFalse(
            not(lit(true))->eval(Row::create())
        );
    }

    public function test_not_expression_on_is_in_expression() : void
    {
        self::assertTrue(
            not(ref('value')->isIn(ref('array')))->eval(Row::create(json_entry('array', [1, 2, 3]), int_entry('value', 10)))
        );
    }
}
