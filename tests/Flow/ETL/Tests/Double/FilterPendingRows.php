<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Double;

use Flow\ETL\Row;
use Flow\ETL\Transformer\Filter;

final class FilterPendingRows implements Filter
{
    public function __invoke(Row $row) : bool
    {
        return $row->valueOf('status') !== 'PENDING';
    }
}
