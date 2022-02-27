<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\Exception\RuntimeException;
use Flow\ETL\Row\Entry;
use Flow\ETL\Transformer\Condition as TransformerCondition;
use Flow\ETL\Transformer\Condition\RowCondition;
use Symfony\Component\Validator\Constraint;

final class Condition
{
    public static function all(RowCondition ...$conditions) : RowCondition
    {
        return new TransformerCondition\All(...$conditions);
    }

    public static function any(RowCondition ...$conditions) : RowCondition
    {
        return new TransformerCondition\Any(...$conditions);
    }

    public static function array_exists(string $entry, string $path) : RowCondition
    {
        return new TransformerCondition\ArrayDotExists($entry, $path);
    }

    /**
     * @param string $entry
     * @param string $path
     * @param mixed $value
     * @param bool $identical
     */
    public static function array_value_equals(string $entry, string $path, $value, bool $identical = true) : RowCondition
    {
        return new TransformerCondition\ArrayDotValueEqualsTo($entry, $path, $value, $identical);
    }

    /**
     * @param string $entry
     * @param string $path
     * @param mixed $value
     */
    public static function array_value_greater_or_equal(string $entry, string $path, $value) : RowCondition
    {
        return new TransformerCondition\ArrayDotValueGreaterOrEqualThan($entry, $path, $value);
    }

    /**
     * @param string $entry
     * @param string $path
     * @param mixed $value
     */
    public static function array_value_greater_than(string $entry, string $path, $value) : RowCondition
    {
        return new TransformerCondition\ArrayDotValueGreaterThan($entry, $path, $value);
    }

    /**
     * @param string $entry
     * @param string $path
     * @param mixed $value
     */
    public static function array_value_less_or_equal(string $entry, string $path, $value) : RowCondition
    {
        return new TransformerCondition\ArrayDotValueLessOrEqualThan($entry, $path, $value);
    }

    /**
     * @param string $entry
     * @param string $path
     * @param mixed $value
     */
    public static function array_value_less_than(string $entry, string $path, $value) : RowCondition
    {
        return new TransformerCondition\ArrayDotValueLessThan($entry, $path, $value);
    }

    /**
     * @param string $entry
     * @param mixed $value
     * @param bool $identical
     */
    public static function equals_value(string $entry, $value, bool $identical = true) : RowCondition
    {
        return new TransformerCondition\EntryValueEqualsTo($entry, $value, $identical);
    }

    public static function exists(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryExists($entry);
    }

    /**
     * @param string $entry
     * @param mixed $value
     */
    public static function greater_or_equals_value(string $entry, $value) : RowCondition
    {
        return new TransformerCondition\EntryValueGreaterOrEqualThan($entry, $value);
    }

    /**
     * @param string $entry
     * @param mixed $value
     */
    public static function greater_than_value(string $entry, $value) : RowCondition
    {
        return new TransformerCondition\EntryValueGreaterThan($entry, $value);
    }

    public static function is_array(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\ArrayEntry::class);
    }

    public static function is_boolean(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\BooleanEntry::class);
    }

    public static function is_float(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\FloatEntry::class);
    }

    public static function is_integer(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\IntegerEntry::class);
    }

    public static function is_json(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\JsonEntry::class);
    }

    public static function is_not_null(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryNotNull($entry);
    }

    public static function is_null(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\NullEntry::class);
    }

    public static function is_object(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\ObjectEntry::class);
    }

    public static function is_string(string $entry) : RowCondition
    {
        return new TransformerCondition\EntryInstanceOf($entry, Entry\StringEntry::class);
    }

    public static function is_valid(string $entry, Constraint ...$constraints) : RowCondition
    {
        if (!\class_exists('Symfony\Component\Validator\Validation')) {
            throw new RuntimeException("Symfony\Component\Validator\Validation class not found, please add symfony/validator dependency to the project first.");
        }

        return new TransformerCondition\ValidValue($entry, new TransformerCondition\ValidValue\SymfonyValidator($constraints));
    }

    /**
     * @param string $entry
     * @param mixed $value
     */
    public static function less_or_equals_value(string $entry, $value) : RowCondition
    {
        return new TransformerCondition\EntryValueLessOrEqualThan($entry, $value);
    }

    /**
     * @param string $entry
     * @param mixed $value
     */
    public static function less_than_value(string $entry, $value) : RowCondition
    {
        return new TransformerCondition\EntryValueLessThan($entry, $value);
    }

    public static function none(RowCondition ...$conditions) : RowCondition
    {
        return new TransformerCondition\None(...$conditions);
    }

    public static function opposite(RowCondition $condition) : RowCondition
    {
        return new TransformerCondition\Opposite($condition);
    }
}
