<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Rows;

/**
 * @psalm-immutable
 */
final class BulkTransformer implements RowTransformer
{
    /**
     * @var array<RowTransformer>
     */
    private array $transformers;

    public function __construct(RowTransformer ...$transformers)
    {
        $this->transformers = $transformers;
    }

    public function transform(Rows $rows) : Rows
    {
        /** @psalm-suppress InvalidArgument */
        return $rows->map([$this, 'transformRow']);
    }

    public function transformRow(Row $row) : Row
    {
        foreach ($this->transformers as $transformer) {
            $row = $transformer->transformRow($row);
        }

        return $row;
    }
}
