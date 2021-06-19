<?php

declare(strict_types=1);

namespace Flow\ETL\ErrorHandler;

use Flow\ETL\ErrorHandler;
use Flow\ETL\Rows;

final class ContinueHandler implements ErrorHandler
{
    public function handle(\Throwable $error, Rows $rows) : void
    {
    }
}
