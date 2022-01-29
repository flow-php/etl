<?php declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row\Entry;

/**
 * @psalm-immutable
 */
interface EntryTransformer extends RowTransformer
{
    public function transformEntry(Entry $entry) : Entry;
}
