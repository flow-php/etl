<?php declare(strict_types=1);

namespace Flow\ETL;

interface ErrorHandler
{
    /**
     * @param \Throwable $error
     * @param Rows $rows
     *
     * @throws \Throwable
     */
    public function handle(\Throwable $error, Rows $rows) : void;
}
