<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Row\Entry;
use Flow\ETL\Rows;

/**
 * @psalm-immutable
 */
final class BulkTransformer implements EntryTransformer
{
    /**
     * @var array<EntryTransformer|RowTransformer>
     */
    private array $transformers;

    /**
     * @param array<EntryTransformer|RowTransformer> $rowTransformers
     */
    private function __construct(array $rowTransformers = [])
    {
        $this->transformers = $rowTransformers;
    }

    public static function empty() : self
    {
        return new self([]);
    }

    public static function rows(RowTransformer ...$rowTransformers) : self
    {
        return new self($rowTransformers);
    }

    public static function entries(EntryTransformer ...$entryTransformers) : self
    {
        return new self($entryTransformers);
    }

    public function addRow(RowTransformer $rowTransformer) : self
    {
        return new self(\array_merge($this->transformers, [$rowTransformer]));
    }

    public function addEntry(EntryTransformer $entryTransformer) : self
    {
        return new self(\array_merge($this->transformers, [$entryTransformer]));
    }

    public function transform(Rows $rows) : Rows
    {
        /** @psalm-suppress InvalidArgument */
        return $rows->map([$this, 'transformRow']);
    }

    public function transformRow(Row $row) : Row
    {
        foreach ($this->transformers as $transformer) {
            $row = $transformer->transformRow($row);
        }

        return $row->map([$this, 'transformEntry']);
    }

    public function transformEntry(Entry $entry) : Entry
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer instanceof EntryTransformer) {
                $entry = $transformer->transformEntry($entry);
            }
        }

        return $entry;
    }

    public function isEmpty() : bool
    {
        return !(bool) \count($this->transformers);
    }
}
