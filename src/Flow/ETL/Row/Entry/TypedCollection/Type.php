<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry\TypedCollection;

enum Type
{
    case boolean;
    case datetime;
    case float;
    case integer;
    case string;

    /**
     * @psalm-pure
     *
     * @param array<mixed> $collection
     *
     * @return bool
     */
    public function isValid(array $collection) : bool
    {
        /** @psalm-suppress ImpureVariable */
        if ($this === self::datetime) {
            foreach ($collection as $item) {
                if (!$item instanceof \DateTimeInterface) {
                    return false;
                }
            }

            return true;
        }

        /** @psalm-suppress ImpureVariable */
        $types = $this->types($collection);

        if (\count($types) === 1) {
            /** @var string $type */
            $type = \current($types) === 'double' ? 'float' : \current($types);

            /** @psalm-suppress ImpureVariable */
            return $this->name === $type;
        }

        return false;
    }

    /**
     * @psalm-pure
     *
     * @param array<mixed> $collection
     *
     * @return array<string>
     */
    public function types(array $collection) : array
    {
        /** @var array<string> $types */
        $types = [];

        /** @psalm-suppress MixedAssignment */
        foreach ($collection as $value) {
            $type = \gettype($value);

            if ($value instanceof \DateTimeInterface) {
                $type = 'datetime';
            }

            if ($type === 'double') {
                $type = 'float';
            }

            $types[] = $type;
        }

        return \array_values(\array_unique($types));
    }
}
