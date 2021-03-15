<?php

declare(strict_types=1);

namespace Flow\ETL;

use Flow\ETL\Exception\RuntimeException;

final class RowsBucket
{
    private int $limit;

    /**
     * @var Row[]
     */
    private array $rows;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
        $this->rows = [];
    }

    public function add(Row $row) : void
    {
        if ($this->completed()) {
            throw RuntimeException::because('Limit of rows was exceeded');
        }

        $this->rows[] = $row;
    }

    public function completed() : bool
    {
        return \count($this->rows) >= $this->limit;
    }

    public function popAll() : Rows
    {
        $rows = $this->rows;
        $this->rows = [];

        return new Rows(...$rows);
    }
}
