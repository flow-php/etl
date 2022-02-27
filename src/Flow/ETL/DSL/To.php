<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\Formatter;
use Flow\ETL\Loader;
use Flow\ETL\Memory\Memory;

class To
{
    public static function buffer(Loader $overflowLoader, int $bufferSize) : Loader
    {
        return new Loader\BufferLoader($overflowLoader, $bufferSize);
    }

    public static function memory(Memory $memory) : Loader
    {
        return new Loader\MemoryLoader($memory);
    }

    public static function output(int $truncate = 20, Formatter $formatter = null) : Loader
    {
        return Loader\StreamLoader::output($truncate, $formatter);
    }

    public static function stderr(int $truncate = 20, Formatter $formatter = null) : Loader
    {
        return Loader\StreamLoader::stderr($truncate, $formatter);
    }

    public static function stdout(int $truncate = 20, Formatter $formatter = null) : Loader
    {
        return Loader\StreamLoader::stdout($truncate, $formatter);
    }
}
