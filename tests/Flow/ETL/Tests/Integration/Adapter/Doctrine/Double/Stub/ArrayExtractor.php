<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Adapter\Doctrine\Double\Stub;

use Flow\ETL\Extractor;
use Flow\ETL\Row;
use Flow\ETL\Rows;

final class ArrayExtractor implements Extractor
{
    private readonly array $rows;

    public function __construct(array ...$rows)
    {
        $this->rows = $rows;
    }

    public function extract() : \Generator
    {
        yield new Rows(
            ...\array_map(
                fn (array $row) : Row => Row::create(new Row\Entry\ArrayEntry('row', $row)),
                $this->rows
            )
        );
    }
}
