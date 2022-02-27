<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\ErrorHandler as Handler;

class Error
{
    public static function ignore_error() : Handler
    {
        return new Handler\IgnoreError();
    }

    public static function skip_rows() : Handler
    {
        return new Handler\SkipRows();
    }

    public static function throw_error() : Handler
    {
        return new Handler\ThrowError();
    }
}
