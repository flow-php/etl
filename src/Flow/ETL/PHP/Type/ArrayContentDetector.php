<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type;

use function Flow\ETL\DSL\{type_array, type_null};
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\PHP\Type\Native\ScalarType;

final class ArrayContentDetector
{
    private readonly ?Type $firstKeyType;

    private readonly ?Type $firstValueType;

    private bool $isList;

    private readonly int $uniqueKeysTypeCount;

    private readonly int $uniqueValuesTypeCount;

    public function __construct(Types $uniqueKeysType, Types $uniqueValuesType, bool $isList = false)
    {
        $this->firstKeyType = $uniqueKeysType->first();
        $this->firstValueType = $uniqueValuesType->first();
        $this->uniqueKeysTypeCount = $uniqueKeysType->count();
        $this->uniqueValuesTypeCount = $uniqueValuesType->without(type_array(true), type_null())->count();
        $this->isList = $isList;
    }

    public function firstKeyType() : ?ScalarType
    {
        if (null !== $this->firstKeyType && !$this->firstKeyType instanceof ScalarType) {
            throw InvalidArgumentException::because('First unique key type must be of ScalarType, given: ' . $this->firstKeyType::class);
        }

        return $this->firstKeyType;
    }

    public function firstValueType() : ?Type
    {
        return $this->firstValueType;
    }

    public function isList() : bool
    {
        return 1 === $this->uniqueValuesTypeCount && $this->firstKeyType()?->isInteger() && $this->isList;
    }

    public function isMap() : bool
    {
        return 1 === $this->uniqueValuesTypeCount && 1 === $this->uniqueKeysTypeCount && !$this->isList;
    }

    public function isStructure() : bool
    {
        if ($this->isList() || $this->isMap()) {
            return false;
        }

        return 0 !== $this->uniqueValuesTypeCount
            && 1 === $this->uniqueKeysTypeCount
            && $this->firstKeyType()?->isString();
    }
}
