<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\Extractor;
use Flow\ETL\Extractor\MemoryExtractor;
use Flow\ETL\Extractor\ProcessExtractor;
use Flow\ETL\Memory\ArrayMemory;
use Flow\ETL\Rows;

class From
{
    /**
     * @param array<array> $array
     * @param int $batch_size
     * @param string $entry_row_name
     */
    public static function array(array $array, int $batch_size = 100, $entry_row_name = 'row') : Extractor
    {
        return new MemoryExtractor(new ArrayMemory($array), $batch_size, $entry_row_name);
    }

    /**
     * @param Extractor $extractor
     * @param int $maxRowsSize
     *
     * @return Extractor
     */
    public static function buffer(Extractor $extractor, int $maxRowsSize) : Extractor
    {
        return new Extractor\BufferExtractor($extractor, $maxRowsSize);
    }

    public static function chain(Extractor ...$extractors) : Extractor
    {
        return new Extractor\ChainExtractor(...$extractors);
    }

    /**
     * @param Rows ...$rows
     *
     * @return Extractor
     */
    public static function rows(Rows ...$rows) : Extractor
    {
        return new ProcessExtractor($rows);
    }
}
