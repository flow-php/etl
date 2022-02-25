<?php

declare(strict_types=1);

namespace Flow\ETL\ExternalSort;

use Flow\ETL\Cache;
use Flow\ETL\ExternalSort;
use Flow\ETL\Extractor;
use Flow\ETL\Monitoring\Memory\Configuration;
use Flow\ETL\Monitoring\Memory\Consumption;
use Flow\ETL\Monitoring\Memory\Unit;
use Flow\ETL\Row\Sort;
use Flow\ETL\Rows;

final class MemorySort implements ExternalSort
{
    private Unit $maximumMemory;

    private Configuration $configuration;

    private Cache $cache;

    private string $cacheId;

    public function __construct(
        string $cacheId,
        Cache $cache,
        Unit $maximumMemory
    ) {
        $this->cache = $cache;
        $this->cacheId = $cacheId;
        $this->maximumMemory = $maximumMemory;
        $this->configuration = new Configuration();

        if ($this->configuration->isLessThan($maximumMemory)) {
            $this->maximumMemory = $this->configuration->limit()->percentage(10);
        }
    }

    public function sortBy(Sort ...$entries) : Extractor
    {
        $memoryConsumption = new Consumption();

        $mergedRows = new Rows();
        $maxSize = 0;

        foreach ($this->cache->read($this->cacheId) as $rows) {
            $maxSize = \max($rows->count(), $maxSize);
            $mergedRows = $mergedRows->merge($rows);

            if ($memoryConsumption->currentDiff()->inBytes() > $this->maximumMemory->inBytes()) {
                // Reset already merged rows and fallback to Cache based External Sort
                $mergedRows = new Rows();

                return (new CacheExternalSort($this->cacheId, $this->cache))->sortBy(...$entries);
            }
        }

        $this->cache->clear($this->cacheId);

        return new Extractor\ProcessExtractor(...$mergedRows->sortBy(...$entries)->chunks($maxSize));
    }
}
