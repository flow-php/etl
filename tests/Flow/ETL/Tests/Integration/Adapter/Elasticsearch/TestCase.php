<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Adapter\Elasticsearch;

use Flow\ETL\Tests\Integration\Adapter\Elasticsearch\Context\ElasticsearchContext;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ElasticsearchContext $elasticsearchContext;

    protected function setUp() : void
    {
        $this->elasticsearchContext = new ElasticsearchContext([\getenv('ELASTICSEARCH_URL')]);
    }
}
