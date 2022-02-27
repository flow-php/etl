<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use Flow\ETL\Extractor;
use Flow\ETL\Extractor\ChainExtractor;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use PHPUnit\Framework\TestCase;

final class ChainExtractorTest extends TestCase
{
    public function test_chain_extractor() : void
    {
        $extractor = new ChainExtractor(
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 1)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 2)));
                }
            },
            new class implements Extractor {
                public function extract() : \Generator
                {
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 3)));
                    yield new Rows(Row::create(new Row\Entry\IntegerEntry('id', 4)));
                }
            },
        );

        $this->assertEquals(
            [
                new Rows(Row::create(new Row\Entry\IntegerEntry('id', 1))),
                new Rows(Row::create(new Row\Entry\IntegerEntry('id', 2))),
                new Rows(Row::create(new Row\Entry\IntegerEntry('id', 3))),
                new Rows(Row::create(new Row\Entry\IntegerEntry('id', 4))),
            ],
            \iterator_to_array($extractor->extract())
        );
    }
}
