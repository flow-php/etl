<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry;

use Flow\ArrayComparison\ArrayWeakComparison;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\Row\Entries;
use Flow\ETL\Row\Entry;

/**
 * @psalm-immutable
 */
final class CollectionEntry implements Entry
{
    private string $key;

    private string $name;

    /**
     * @var Entries[]
     */
    private array $entries;

    public function __construct(string $name, Entries ...$entries)
    {
        if (empty($name)) {
            throw InvalidArgumentException::because('Entry name cannot be empty');
        }

        $this->key = \mb_strtolower($name);
        $this->name = $name;
        $this->entries = $entries;
    }

    public function name() : string
    {
        return $this->name;
    }

    /**
     * @psalm-suppress MissingReturnType
     * @phpstan-ignore-next-line
     */
    public function value() : array
    {
        return \array_map(fn (Entries $entries) : array => $entries->toArray(), $this->entries);
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function is(string $name) : bool
    {
        return $this->key === \mb_strtolower($name);
    }

    public function rename(string $name) : Entry
    {
        return new self($name, ...$this->entries);
    }

    /**
     * @psalm-suppress MixedArgument
     */
    public function map(callable $mapper) : Entry
    {
        return new self($this->name, ...$mapper($this->entries));
    }

    public function isEqual(Entry $entry) : bool
    {
        return $this->is($entry->name()) && $entry instanceof self && (new ArrayWeakComparison())->equals($this->value(), $entry->value());
    }
}
