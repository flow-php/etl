<?php declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Transformer;

/**
 * @psalm-immutable
 */
interface MergeableTransformer extends Transformer
{
    public function transformOne(Row $row) : Row;
}
