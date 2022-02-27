<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Loader;

use Flow\ETL\Loader;
use Flow\ETL\Loader\BufferLoader;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use PHPUnit\Framework\TestCase;

final class BufferLoaderTest extends TestCase
{
    public function test_buffer_extractor_when_less_than_max_rows_size() : void
    {
        $overflowLoader = $this->createMock(Loader::class);

        $loader = new BufferLoader(
            $overflowLoader,
            10
        );

        $overflowLoader->expects($this->never())
            ->method('load');

        $loader->load(
            new Rows(
                Row::create(new Row\Entry\IntegerEntry('id', 1)),
                Row::create(new Row\Entry\IntegerEntry('id', 2)),
            )
        );
    }

    public function test_buffer_extractor_when_less_than_max_rows_size_with_closure() : void
    {
        $overflowLoader = $this->createMock(Loader::class);

        $loader = new BufferLoader(
            $overflowLoader,
            10
        );

        $overflowLoader->expects($this->once())
            ->method('load')
            ->with(
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                )
            );

        $loader->load(
            new Rows(
                Row::create(new Row\Entry\IntegerEntry('id', 1)),
                Row::create(new Row\Entry\IntegerEntry('id', 2)),
            )
        );
        $loader->closure(
            new Rows(
                Row::create(new Row\Entry\IntegerEntry('id', 1)),
                Row::create(new Row\Entry\IntegerEntry('id', 2)),
            )
        );
    }

    public function test_buffer_extractor_with_equal_max_rows_size_multiple_times() : void
    {
        $overflowLoader = $this->createMock(Loader::class);

        $loader = new BufferLoader(
            $overflowLoader,
            4
        );

        $overflowLoader->expects($this->once())
            ->method('load')
            ->with(
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                    Row::create(new Row\Entry\IntegerEntry('id', 3)),
                    Row::create(new Row\Entry\IntegerEntry('id', 4)),
                )
            );

        $loader->load(
            new Rows(
                Row::create(new Row\Entry\IntegerEntry('id', 1)),
                Row::create(new Row\Entry\IntegerEntry('id', 2)),
            )
        );

        $loader->load(
            new Rows(
                Row::create(new Row\Entry\IntegerEntry('id', 3)),
                Row::create(new Row\Entry\IntegerEntry('id', 4)),
            )
        );

        $loader->closure(new Rows());
    }

    public function test_buffer_extractor_with_more_than_max_rows_size() : void
    {
        $overflowLoader = $this->createMock(Loader::class);

        $loader = new BufferLoader(
            $overflowLoader,
            2
        );

        $overflowLoader->expects($this->once())
            ->method('load')
            ->with(
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                )
            );

        $loader->load(
            new Rows(
                Row::create(new Row\Entry\IntegerEntry('id', 1)),
                Row::create(new Row\Entry\IntegerEntry('id', 2)),
                Row::create(new Row\Entry\IntegerEntry('id', 3)),
            )
        );
    }
}
