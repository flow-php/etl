<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit;

use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\GroupBy;
use PHPUnit\Framework\TestCase;

final class GroupByTest extends TestCase
{
    public function test_group_by_with_empty_aggregations() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Aggregations can't be empty");
        $grupBy = new GroupBy();
        $grupBy->aggregate();
    }
}