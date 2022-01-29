<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Double;

use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Transformer\MergeableTransformer;

final class MergeableTransformerStub implements MergeableTransformer
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

    public function transformOne(Row $row) : Row
    {
        return $row;
    }
}
