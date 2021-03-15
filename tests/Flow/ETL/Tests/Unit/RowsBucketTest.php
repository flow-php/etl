<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit;

use Flow\ETL\Exception\RuntimeException;
use Flow\ETL\Row;
use Flow\ETL\RowsBucket;
use PHPUnit\Framework\TestCase;

final class RowsBucketTest extends TestCase
{
    public function test_that_cannon_add_more_rows_than_limit() : void
    {
        $rowsBucket = new RowsBucket(2);
        $rowsBucket->add(Row::create(Row\Entry\IntegerEntry::from('number', 1)));
        $rowsBucket->add(Row::create(Row\Entry\IntegerEntry::from('number', 2)));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Limit of rows was exceeded');

        $rowsBucket->add(Row::create(Row\Entry\IntegerEntry::from('number', 3)));
    }

    public function test_add_row_to_the_bucket() : void
    {
        $rowsBucket = new RowsBucket(2);
        $rowsBucket->add(Row::create(Row\Entry\IntegerEntry::from('number', 1)));
        $rowsBucket->add(Row::create(Row\Entry\IntegerEntry::from('number', 2)));

        $this->assertTrue($rowsBucket->completed());

        $this->assertEquals(
            [
                ['number' => 1],
                ['number' => 2],
            ],
            $rowsBucket->popAll()->toArray()
        );
    }
}
