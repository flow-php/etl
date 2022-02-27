<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\Row\Entry as RowEntry;

final class Entry
{
    /**
     * @param string $name
     * @param array<mixed> $data
     */
    public static function array(string $name, array $data) : RowEntry
    {
        return new RowEntry\ArrayEntry($name, $data);
    }

    public static function boolean(string $name, bool $value) : RowEntry
    {
        return new RowEntry\BooleanEntry($name, $value);
    }

    public static function datetime(string $name, string $value) : RowEntry
    {
        return new RowEntry\DateTimeEntry($name, new \DateTimeImmutable($value));
    }

    public static function float(string $name, float $value) : RowEntry
    {
        return new RowEntry\FloatEntry($name, $value);
    }

    public static function integer(string $name, int $value) : RowEntry
    {
        return new RowEntry\IntegerEntry($name, $value);
    }

    /**
     * @param string $name
     * @param array<mixed> $data
     */
    public static function json(string $name, array $data) : RowEntry
    {
        return new RowEntry\JsonEntry($name, $data);
    }

    /**
     * @param string $name
     * @param array<mixed> $data
     */
    public static function json_object(string $name, array $data) : RowEntry
    {
        return RowEntry\JsonEntry::object($name, $data);
    }

    public static function object(string $name, object $object) : RowEntry
    {
        return new RowEntry\ObjectEntry($name, $object);
    }

    public static function string(string $name, string $value) : RowEntry
    {
        return new RowEntry\StringEntry($name, $value);
    }
}
