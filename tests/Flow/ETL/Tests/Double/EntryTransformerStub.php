<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Double;

use Flow\ETL\Row;
use Flow\ETL\Row\Entry;
use Flow\ETL\Rows;
use Flow\ETL\Transformer\EntryTransformer;

final class EntryTransformerStub implements EntryTransformer
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function transform(Rows $rows) : Rows
    {
        return $rows;
    }

    public function transformRow(Row $row) : Row
    {
        return $row;
    }

    public function transformEntry(Entry $entry) : Entry
    {
        return $entry;
    }
}
