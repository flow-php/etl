<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

/**
 * @psalm-immutable
 */
final class FilterRows implements Transformer
{
    /**
     * @var Filter[]
     */
    private array $filters;

    /**
     * @psalm-suppress ImpurePropertyAssignment
     */
    public function __construct(Filter ...$filters)
    {
        $this->filters = $filters;
    }

    public function transform(Rows $rows) : Rows
    {
        return $rows->filter(
            function (Row $row) {
                foreach ($this->filters as $filter) {
                    if (false === $filter($row)) {
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
