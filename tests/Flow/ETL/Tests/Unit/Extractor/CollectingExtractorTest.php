<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\Extractor;
use Flow\ETL\Extractor\CollectingExtractor;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use PHPUnit\Framework\TestCase;

final class CollectingExtractorTest extends TestCase
{
    public function test_collecting_extractor_when_less_than_max_rows_size() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(
                        Row::create(new Row\Entry\IntegerEntry('id', 1)),
                        Row::create(new Row\Entry\IntegerEntry('id', 2)),
                    );
                }
            },
            10
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }

    public function test_collecting_extractor_when_equals_to_max_rows_size() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(
                        Row::create(new Row\Entry\IntegerEntry('id', 1)),
                        Row::create(new Row\Entry\IntegerEntry('id', 2)),
                    );
                }
            },
            2
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }

    public function test_collecting_extractor_when_more_even_than_max_rows_size() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(
                        Row::create(new Row\Entry\IntegerEntry('id', 1)),
                        Row::create(new Row\Entry\IntegerEntry('id', 2)),
                        Row::create(new Row\Entry\IntegerEntry('id', 3)),
                        Row::create(new Row\Entry\IntegerEntry('id', 4)),
                    );

                    yield new Rows(
                        Row::create(new Row\Entry\IntegerEntry('id', 5)),
                        Row::create(new Row\Entry\IntegerEntry('id', 6)),
                        Row::create(new Row\Entry\IntegerEntry('id', 7)),
                        Row::create(new Row\Entry\IntegerEntry('id', 8)),
                    );
                }
            },
            2
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 3)),
                    Row::create(new Row\Entry\IntegerEntry('id', 4)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 5)),
                    Row::create(new Row\Entry\IntegerEntry('id', 6)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 7)),
                    Row::create(new Row\Entry\IntegerEntry('id', 8)),
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }

    public function test_collecting_extractor_when_more_odd_than_max_rows_size() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(
                        Row::create(new Row\Entry\IntegerEntry('id', 1)),
                        Row::create(new Row\Entry\IntegerEntry('id', 2)),
                        Row::create(new Row\Entry\IntegerEntry('id', 3)),
                        Row::create(new Row\Entry\IntegerEntry('id', 4)),
                    );

                    yield new Rows(
                        Row::create(new Row\Entry\IntegerEntry('id', 5)),
                        Row::create(new Row\Entry\IntegerEntry('id', 6)),
                        Row::create(new Row\Entry\IntegerEntry('id', 7)),
                    );
                }
            },
            2
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 3)),
                    Row::create(new Row\Entry\IntegerEntry('id', 4)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 5)),
                    Row::create(new Row\Entry\IntegerEntry('id', 6)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 7)),
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }

    public function test_collecting_extractor_from_single_row_rows_even() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 1)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 2)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 3)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 4)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 5)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 6)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 7)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 8)));
                }
            },
            2
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 3)),
                    Row::create(new Row\Entry\IntegerEntry('id', 4))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 5)),
                    Row::create(new Row\Entry\IntegerEntry('id', 6))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 7)),
                    Row::create(new Row\Entry\IntegerEntry('id', 8))
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }

    public function test_collecting_extractor_from_single_row_rows_odd() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 1)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 2)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 3)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 4)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 5)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 6)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 7)));
                }
            },
            2
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 3)),
                    Row::create(new Row\Entry\IntegerEntry('id', 4))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 5)),
                    Row::create(new Row\Entry\IntegerEntry('id', 6))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 7)),
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }

    public function test_collecting_extractor_from_single_row_rows_odd_max_size_odd() : void
    {
        $extractor = new CollectingExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 1)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 2)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 3)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 4)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 5)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 6)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 7)));
                }
            },
            3
        );

        $this->assertEquals(
            [
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 1)),
                    Row::create(new Row\Entry\IntegerEntry('id', 2)),
                    Row::create(new Row\Entry\IntegerEntry('id', 3)),
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 4)),
                    Row::create(new Row\Entry\IntegerEntry('id', 5)),
                    Row::create(new Row\Entry\IntegerEntry('id', 6))
                ),
                new Rows(
                    Row::create(new Row\Entry\IntegerEntry('id', 7)),
                ),
            ],
            \iterator_to_array($extractor->extract())
        );
    }
}
