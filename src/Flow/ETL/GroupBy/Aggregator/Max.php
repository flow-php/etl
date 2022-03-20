<?php

declare(strict_types=1);

namespace Flow\ETL\GroupBy\Aggregator;

use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\GroupBy\Aggregator;
use Flow\ETL\Row;
use Flow\ETL\Row\Entry;

final class Max implements Aggregator
{
    private string $entry;

    private float $max;

    public function __construct(string $entry)
    {
        $this->entry = $entry;
        $this->max = 0;
    }

    public function aggregate(Row $row) : void
    {
        try {
            $value = $row->valueOf($this->entry);

            if (\is_numeric($value)) {
                $this->max = \max($this->max, (float) $value);
            }
        } catch (InvalidArgumentException $e) {
            // do nothing?
        }
    }

    public function result() : Entry
    {
        $resultInt = (int) $this->max;

        if ($this->max - $resultInt === 0.0) {
            return \Flow\ETL\DSL\Entry::integer($this->entry . '_max', (int) $this->max);
        }

        return \Flow\ETL\DSL\Entry::float($this->entry . '_max', $this->max);
    }
}
