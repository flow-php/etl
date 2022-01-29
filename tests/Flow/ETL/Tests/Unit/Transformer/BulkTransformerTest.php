<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Row\Entry;
use Flow\ETL\Rows;
use Flow\ETL\Transformer\BulkTransformer;
use Flow\ETL\Transformer\EntryTransformer;
use Flow\ETL\Transformer\RowTransformer;
use PHPUnit\Framework\TestCase;

final class BulkTransformerTest extends TestCase
{
    public function test_transforming_rows_in_bulk() : void
    {
        $transformer = BulkTransformer::rows(
            new class implements RowTransformer {
                public function transform(Rows $rows) : Rows
                {
                    throw new \RuntimeException('should not be used!');
                }

                public function transformRow(Row $row) : Row
                {
                    return $row->add(new Row\Entry\IntegerEntry('row_1', \random_int(0, 100)));
                }
            },
            new class implements RowTransformer {
                public function transform(Rows $rows) : Rows
                {
                    throw new \RuntimeException('should not be used!');
                }

                public function transformRow(Row $row) : Row
                {
                    return $row->add(new Row\Entry\IntegerEntry('row_2', \random_int(0, 100)));
                }
            },
            new class implements RowTransformer {
                public function transform(Rows $rows) : Rows
                {
                    throw new \RuntimeException('should not be used!');
                }

                public function transformRow(Row $row) : Row
                {
                    return $row->add(new Row\Entry\IntegerEntry('row_3', \random_int(0, 100)));
                }
            }
        );

        $rows = $transformer->transform(new Rows(Row::create()));

        $this->assertCount(3, $rows->first()->entries());
        $this->assertTrue($rows->first()->entries()->has('row_1'));
        $this->assertTrue($rows->first()->entries()->has('row_2'));
        $this->assertTrue($rows->first()->entries()->has('row_3'));
    }

    public function test_transforming_entries_in_bulk() : void
    {
        $transformer = BulkTransformer::rows(
            new class implements EntryTransformer {
                public function transform(Rows $rows) : Rows
                {
                    throw new \RuntimeException('should not be used!');
                }

                public function transformRow(Row $row) : Row
                {
                    return $row->add(new Row\Entry\IntegerEntry('row_1', \random_int(0, 100)));
                }

                public function transformEntry(Entry $entry) : Entry
                {
                    return $entry->is('row_1') ? $entry->rename('new_row_1') : $entry;
                }
            },
            new class implements EntryTransformer {
                public function transform(Rows $rows) : Rows
                {
                    throw new \RuntimeException('should not be used!');
                }

                public function transformRow(Row $row) : Row
                {
                    return $row->add(new Row\Entry\IntegerEntry('row_2', \random_int(0, 100)));
                }

                public function transformEntry(Entry $entry) : Entry
                {
                    return $entry->is('row_2') ? $entry->rename('new_row_2') : $entry;
                }
            },
            new class implements EntryTransformer {
                public function transform(Rows $rows) : Rows
                {
                    throw new \RuntimeException('should not be used!');
                }

                public function transformRow(Row $row) : Row
                {
                    return $row->add(new Row\Entry\IntegerEntry('row_3', \random_int(0, 100)));
                }

                public function transformEntry(Entry $entry) : Entry
                {
                    return $entry->is('row_3') ? $entry->rename('new_row_3') : $entry;
                }
            }
        );

        $rows = $transformer->transform(new Rows(Row::create()));

        $this->assertCount(3, $rows->first()->entries());
        $this->assertTrue($rows->first()->entries()->has('new_row_1'));
        $this->assertTrue($rows->first()->entries()->has('new_row_2'));
        $this->assertTrue($rows->first()->entries()->has('new_row_3'));
    }
}
