<?php

declare(strict_types=1);

namespace Flow\ETL\Extractor;

use Flow\ETL\Extractor;

final class ChainExtractor implements Extractor
{
    /**
     * @var array<Extractor>
     */
    private array $extractors;

    public function __construct(Extractor ...$extractors)
    {
        $this->extractors = $extractors;
    }

    public function extract() : \Generator
    {
        foreach ($this->extractors as $extractor) {
            foreach ($extractor->extract() as $rows) {
                yield $rows;
            }
        }
    }
}
