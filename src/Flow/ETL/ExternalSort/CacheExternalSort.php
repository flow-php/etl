<?php

declare(strict_types=1);

namespace Flow\ETL\ExternalSort;

use Flow\ETL\Cache;
use Flow\ETL\ExternalSort;
use Flow\ETL\Extractor;
use Flow\ETL\Extractor\CacheExtractor;
use Flow\ETL\Extractor\CollectingExtractor;
use Flow\ETL\Row\Sort;
use Flow\ETL\Rows;

/**
 * External sorting is explained here:
 * https://medium.com/outco/how-to-merge-k-sorted-arrays-c35d87aa298e.
 */
final class CacheExternalSort implements ExternalSort
{
    private Cache $cache;

    private string $id;

    public function __construct(string $id, Cache $cache)
    {
        $this->cache = $cache;
        $this->id = $id;
    }

    public function sortBy(Sort ...$entries) : Extractor
    {
        /** @var array<string, \Generator<int, Rows, mixed, void>> $cachedPartsArray */
        $cachedPartsArray = [];
        $maxRowsSize = 0;

        foreach ($this->cache->read($this->id) as $i => $rows) {
            $maxRowsSize = \max($maxRowsSize, $rows->count());
            /** @var Rows $singleRowRows */
            $partId = $this->id . '_tmp_' . $i;

            foreach ($rows->sortBy(...$entries)->chunks(1) as $singleRowRows) {
                $this->cache->add($partId, $singleRowRows);
            }

            $cachedPartsArray[$partId] = $this->cache->read($partId);
        }

        $this->cache->clear($this->id);

        $cachedParts = new CachedParts($cachedPartsArray);

        $minHeap = $cachedParts->createHeap(...$entries);

        while ($cachedParts->notEmpty() || !$minHeap->isEmpty()) {
            $cachedParts->takeNext($minHeap, $this->id, $this->cache);
        }

        foreach ($cachedParts->cacheIds() as $cacheId) {
            $this->cache->clear($cacheId);
        }

        return new CollectingExtractor(new CacheExtractor($this->id, $this->cache), $maxRowsSize);
    }
}
