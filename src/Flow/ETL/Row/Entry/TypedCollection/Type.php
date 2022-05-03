<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry\TypedCollection;

enum Type
{
    case boolean;
    case dateTime;
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
        if ($this === self::dateTime) {
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
        return \array_map(
            fn (string $value) => $value === 'double' ? 'float' : $value,
            \array_unique(\array_map('gettype', $collection))
        );
    }
}
