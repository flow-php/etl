<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer\Cast\ValueCaster;

use Flow\ETL\Transformer\Cast\ValueCaster;

/**
 * @psalm-immutable
 */
final class AnyToIntegerCaster implements ValueCaster
{
    public function __serialize() : array
    {
        return [];
    }

    public function __unserialize(array $data) : void
    {
    }

    public function cast($value) : int
    {
        /** @phpstan-ignore-next-line */
        return (int) $value;
    }
}