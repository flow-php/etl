<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry\TypedCollection;

interface Type
{
    public function isEqual(self $type) : bool;

    public function isValid(array $collection) : bool;

    public function toString() : string;
}
