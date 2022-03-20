<?php

declare(strict_types=1);

namespace Flow\ETL;

use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\GroupBy\Aggregation;
use Flow\ETL\GroupBy\Aggregator;
use Flow\ETL\Row\Entries;
use Flow\ETL\Row\Factory\NativeEntryFactory;

final class GroupBy
{
    /**
     * @var array<Aggregation>
     */
    private array $aggregations;

    /**
     * @var array<string>
     */
    private array $entries;

    /**
     * @var array<string, array{?value: mixed, aggregators: array<array-key, Aggregator>}>
     */
    private array $groups;

    public function __construct(string ...$entries)
    {
        $this->entries = $entries;
        $this->aggregations = [];
        $this->groups = [];
    }

    public function aggregate(Aggregation ...$aggregations) : void
    {
        if (!\count($aggregations)) {
            throw new InvalidArgumentException("Aggregations can't be empty");
        }

        $this->aggregations = $aggregations;
    }

    public function group(Rows $rows) : void
    {
        /** @var Row $row */
        foreach ($rows as $row) {
            $values = [];

            foreach ($this->entries as $entryName) {
                try {
                    $values[$entryName] = $row->valueOf($entryName);
                } catch (InvalidArgumentException $e) {
                    $values[$entryName] = null;
                }
            }

            $valuesHash = $this->hash($values);

            if (!\array_key_exists($valuesHash, $this->groups)) {
                $this->groups[$valuesHash] = [
                    'values' => $values,
                    'aggregators' => [],
                ];

                foreach ($this->aggregations as $aggregation) {
                    $this->groups[$valuesHash]['aggregators'][] = $aggregation->create();
                }
            }

            foreach ($this->groups[$valuesHash]['aggregators'] as $aggregator) {
                $aggregator->aggregate($row);
            }
        }
    }

    public function result() : Rows
    {
        $rows = new Rows();

        foreach ($this->groups as $group) {
            $entries = new Entries();

            if (\array_key_exists('values', $group)) {
                foreach ($group['values'] as $entry => $value) {
                    $entries = $entries->add((new NativeEntryFactory)->create($entry, $value));
                }
            }

            foreach ($group['aggregators'] as $aggregator) {
                $entries = $entries->add($aggregator->result());
            }

            if (\count($entries)) {
                $rows = $rows->add(Row::create(...$entries));
            }
        }

        return $rows;
    }

    /**
     * @param array<mixed> $values
     *
     * @return string
     */
    private function hash(array $values) : string
    {
        /** @var array<string> $stringValues */
        $stringValues = [];

        foreach ($values as $value) {
            if ($value === null) {
                $stringValues[] =\hash('sha256', 'null');
            } else {
                $stringValues[] = (string) $value;
            }
        }

        return \hash('sha256', \implode('', $stringValues));
    }
}
