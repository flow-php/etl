<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Adapter\Elasticsearch\Context;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;

final class ElasticsearchContext
{
    private ?Client $client = null;

    public function __construct(private readonly array $hosts)
    {
    }

    public function client() : Client
    {
        if ($this->client === null) {
            $this->client = ClientBuilder::fromConfig($this->clientConfig());
        }

        return $this->client;
    }

    public function clientConfig() : array
    {
        return [
            'hosts' => $this->hosts,
        ];
    }

    public function createIndex(string $name) : void
    {
        try {
            $params = [
                'index' => $name,
                'body'  => [
                    'settings' => [
                        'number_of_shards' => 2,
                        'number_of_replicas' => 0,
                    ],
                ],
            ];

            $response = $this->client()->indices()->create($params);
        } catch (BadRequest400Exception) {
        }
    }

    public function deleteIndex(string $name) : void
    {
        try {
            $deleteParams = [
                'index' => $name,
            ];
            $response = $this->client()->indices()->delete($deleteParams);
        } catch (BadRequest400Exception) {
        }
    }
}
