<?php

declare(strict_types=1);

namespace Flow\ETL\Extractor;

use Flow\ETL\Cache;
use Flow\ETL\Extractor;
use Flow\ETL\Rows;

/**
 * @psalm-immutable
 */
final class CacheExtractor implements Extractor
{
    private string $id;

    private Cache $cache;

    public function __construct(string $id, Cache $cache)
    {
        $this->cache = $cache;
        $this->id = $id;
    }

    /**
     * @psalm-suppress ImpureMethodCall
     *
     * @return \Generator<int, Rows, mixed, void>
     */
    public function extract() : \Generator
    {
        foreach ($this->cache->read($this->id) as $rows) {
            yield $rows;
        }

        $this->cache->clear($this->id);
    }
}
