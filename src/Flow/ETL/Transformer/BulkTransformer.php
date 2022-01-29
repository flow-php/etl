<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Rows;

/**
 * @psalm-immutable
 */
final class BulkTransformer implements MergeableTransformer
{
    /**
     * @var array<MergeableTransformer>
     */
    private array $transformers;

    public function __construct(MergeableTransformer ...$transformers)
    {
        $this->transformers = $transformers;
    }

    public static function empty() : self
    {
        return new self();
    }

    public function add(MergeableTransformer $rowTransformer) : self
    {
        return new self(...\array_merge($this->transformers, [$rowTransformer]));
    }

    public function transform(Rows $rows) : Rows
    {
        /** @psalm-suppress InvalidArgument */
        return $rows->map([$this, 'transformOne']);
    }

    public function transformOne(Row $row) : Row
    {
        foreach ($this->transformers as $transformer) {
            $row = $transformer->transformOne($row);
        }

        return $row;
    }

    public function isEmpty() : bool
    {
        return !(bool) \count($this->transformers);
    }
}
