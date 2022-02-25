<?php

declare(strict_types=1);

namespace Flow\ETL\Monitoring\Memory;

final class Configuration
{
    private ?Unit $limit;

    public function __construct()
    {
        $limitConfig = \ini_get('memory_limit');

        if (\strpos($limitConfig, '-') === 0) {
            $this->limit = null;
        } else {
            $this->limit = Unit::fromString($limitConfig);
        }
    }

    public function limit() : ?Unit
    {
        return $this->limit;
    }

    public function isConsumptionBelow(Unit $unit, int $limitPercentage) : bool
    {
        // if memory is unlimited then current consumption is always below certain threshold
        if ($this->limit === null) {
            return true;
        }

        return (($unit->inBytes() / $this->limit->inBytes()) * 100) < $limitPercentage;
    }

    public function isLessThan(Unit $memory) : bool
    {
        if ($this->limit === null) {
            return false;
        }

        return $this->limit->inBytes() < $memory->inBytes();
    }
}
