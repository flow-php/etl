<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Tests\Double\FilterPendingRows;
use Flow\ETL\Tests\Double\FilterShippedRows;
use Flow\ETL\Transformer\FilterRows;
use PHPUnit\Framework\TestCase;

final class FilterRowsTest extends TestCase
{
    public function test_filter_rows() : void
    {
        $filterRows = new FilterRows(
            new FilterPendingRows(),
            new FilterShippedRows()
        );

        $rows = $filterRows->transform(
            new Rows(
                Row::create(new Row\Entry\StringEntry('status', 'PENDING')),
                Row::create(new Row\Entry\StringEntry('status', 'SHIPPED')),
                Row::create(new Row\Entry\StringEntry('status', 'NEW')),
            )
        );

        $this->assertEquals(
            [
                ['status' => 'NEW'],
            ],
            $rows->toArray()
        );
    }
}
