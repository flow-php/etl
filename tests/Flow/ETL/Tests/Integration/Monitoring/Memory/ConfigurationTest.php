<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Monitoring\Memory;

use Flow\ETL\Monitoring\Memory\Configuration;
use Flow\ETL\Monitoring\Memory\Unit;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    private string $baseMemoryLimit;

    protected function setUp() : void
    {
        $this->baseMemoryLimit = \ini_get('memory_limit');
    }

    protected function tearDown() : void
    {
        if (\ini_get('memory_limit') !== $this->baseMemoryLimit) {
            \ini_set('memory_limit', $this->baseMemoryLimit);
        }
    }

    public function test_memory_limit_fixed() : void
    {
        \ini_set('memory_limit', '1G');

        $config = new Configuration();

        $this->assertEquals(Unit::fromGb(1), $config->limit());
    }

    public function test_memory_limit_infinite() : void
    {
        \ini_set('memory_limit', '-1');

        $config = new Configuration();

        $this->assertNull($config->limit());
    }

    public function test_unit_below_limit_percentage_for_infinite_memory() : void
    {
        \ini_set('memory_limit', '-1');

        $config = new Configuration();

        $this->assertTrue($config->isConsumptionBelow(Unit::fromGb(1000000), 10));
    }

    public function test_unit_below_limit_percentage_for_fixed_memory() : void
    {
        \ini_set('memory_limit', '1G');

        $config = new Configuration();

        $this->assertTrue($config->isConsumptionBelow(Unit::fromMb(100), 10));
        $this->assertFalse($config->isConsumptionBelow(Unit::fromMb(200), 10));
    }
}
