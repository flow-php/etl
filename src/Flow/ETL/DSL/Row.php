<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\Exception\RuntimeException;
use Flow\ETL\Row as ETLRow;
use Flow\ETL\Transformer;
use Flow\ETL\Transformer\ArrayKeysStyleConverterTransformer;
use Flow\ETL\Transformer\Cast\CastJsonToArray;
use Flow\ETL\Transformer\Cast\CastToDateTime;
use Flow\ETL\Transformer\Cast\CastToInteger;
use Flow\ETL\Transformer\Cast\CastToJson;
use Flow\ETL\Transformer\Cast\CastToString;
use Flow\ETL\Transformer\Cast\EntryCaster\DateTimeToStringEntryCaster;
use Flow\ETL\Transformer\Cast\EntryCaster\StringToDateTimeEntryCaster;
use Flow\ETL\Transformer\CastTransformer;
use Flow\ETL\Transformer\Filter\Filter\Callback;
use Flow\ETL\Transformer\Filter\Filter\EntryEqualsTo;
use Flow\ETL\Transformer\Filter\Filter\EntryExists;
use Flow\ETL\Transformer\Filter\Filter\EntryNotNull;
use Flow\ETL\Transformer\Filter\Filter\EntryNumber;
use Flow\ETL\Transformer\Filter\Filter\Opposite;
use Flow\ETL\Transformer\Filter\Filter\ValidValue;
use Flow\ETL\Transformer\FilterRowsTransformer;
use Flow\ETL\Transformer\KeepEntriesTransformer;
use Flow\ETL\Transformer\Rename\ArrayKeyRename;
use Flow\ETL\Transformer\Rename\EntryRename;
use Flow\ETL\Transformer\RenameEntriesTransformer;
use Laminas\Hydrator\ReflectionHydrator;
use Symfony\Component\Validator\Constraint;

final class Row
{
    /**
     * @param string $name
     * @param array<mixed> $data
     */
    public static function add_array(string $name, array $data) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::array($name, $data));
    }

    public static function add_boolean(string $name, bool $value) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::boolean($name, $value));
    }

    public static function add_datetime(string $name, string $value) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::datetime($name, $value));
    }

    public static function add_float(string $name, float $value) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::float($name, $value));
    }

    public static function add_integer(string $name, int $value) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::integer($name, $value));
    }

    /**
     * @param string $name
     * @param array<mixed> $data
     */
    public static function add_json(string $name, array $data) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::json($name, $data));
    }

    /**
     * @param string $name
     * @param array<mixed> $data
     */
    public static function add_json_object(string $name, array $data) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::json_object($name, $data));
    }

    public static function add_object(string $name, object $data) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::object($name, $data));
    }

    public static function add_string(string $name, string $value) : Transformer
    {
        return new Transformer\StaticEntryTransformer(Entry::string($name, $value));
    }

    public static function array_convert_keys(string $array_column, string $style) : Transformer
    {
        if (!\class_exists('Jawira\CaseConverter\Convert')) {
            throw new RuntimeException("Jawira\CaseConverter\Convert class not found, please require using 'composer require jawira/case-converter'");
        }

        return new ArrayKeysStyleConverterTransformer($array_column, $style);
    }

    public static function array_expand(string $array_column, string $expanded_name = 'entry') : Transformer
    {
        return new Transformer\ArrayExpandTransformer($array_column, $expanded_name);
    }

    public static function array_get(string $array_name, string $path, string $entry_name = 'entry') : Transformer
    {
        return new Transformer\ArrayDotGetTransformer($array_name, $path, $entry_name);
    }

    /**
     * @param string[] $array_names
     * @param string $entry_name
     */
    public static function array_merge(array $array_names, string $entry_name = 'entry') : Transformer
    {
        return new Transformer\ArrayMergeTransformer($array_names, $entry_name);
    }

    public static function array_rename_keys(string $array_column, string $path, string $new_name) : Transformer
    {
        return new Transformer\ArrayDotRenameTransformer(new ArrayKeyRename($array_column, $path, $new_name));
    }

    public static function array_reverse(string $array_name) : Transformer
    {
        return new Transformer\ArrayReverseTransformer($array_name);
    }

    public static function array_sort(string $array_name, int $sort_flag = \SORT_REGULAR) : Transformer
    {
        return new Transformer\ArraySortTransformer($array_name, $sort_flag);
    }

    /**
     * @param string $array_column
     * @param string $entry_prefix
     * @param string[] $skip_keys
     */
    public static function array_unpack(string $array_column, string $entry_prefix = '', array $skip_keys = []) : Transformer
    {
        return new Transformer\ArrayUnpackTransformer($array_column, $skip_keys, $entry_prefix);
    }

    public static function chain(Transformer ...$transformers) : Transformer
    {
        return new Transformer\ChainTransformer(...$transformers);
    }

    public static function clone_entry(string $from, string $to) : Transformer
    {
        return new Transformer\CloneEntryTransformer($from, $to);
    }

    public static function convert_name(string $style) : Transformer
    {
        if (!\class_exists('Jawira\CaseConverter\Convert')) {
            throw new RuntimeException("Jawira\CaseConverter\Convert class not found, please require using 'composer require jawira/case-converter'");
        }

        return new Transformer\EntryNameStyleConverterTransformer($style);
    }

    /**
     * @param string $entry
     * @param callable(mixed) : bool $filter
     */
    public static function filter(string $entry, callable $filter) : Transformer
    {
        return new FilterRowsTransformer(new Callback(fn (ETLRow $row) : bool => $filter($row->valueOf($entry))));
    }

    /**
     * @param string $entry
     * @param mixed $value
     */
    public static function filter_equals(string $entry, $value) : Transformer
    {
        return new FilterRowsTransformer(new EntryEqualsTo($entry, $value));
    }

    public static function filter_exists(string $entry) : Transformer
    {
        return new FilterRowsTransformer(new EntryExists($entry));
    }

    public static function filter_invalid(string $entry, Constraint ...$constraints) : Transformer
    {
        return new FilterRowsTransformer(new ValidValue($entry, new ValidValue\SymfonyValidator($constraints)));
    }

    /**
     * @param string $entry
     * @param mixed $value
     */
    public static function filter_not_equals(string $entry, $value) : Transformer
    {
        return new FilterRowsTransformer(new Opposite(new EntryEqualsTo($entry, $value)));
    }

    public static function filter_not_exists(string $entry) : Transformer
    {
        return new FilterRowsTransformer(new Opposite(new EntryExists($entry)));
    }

    public static function filter_not_null(string $entry) : Transformer
    {
        return new FilterRowsTransformer(new EntryNotNull($entry));
    }

    public static function filter_not_number(string $entry) : Transformer
    {
        return new FilterRowsTransformer(new Opposite(new EntryNumber($entry)));
    }

    public static function filter_null(string $entry) : Transformer
    {
        return new FilterRowsTransformer(new Opposite(new EntryNotNull($entry)));
    }

    public static function filter_number(string $entry) : Transformer
    {
        return new FilterRowsTransformer(new EntryNumber($entry));
    }

    public static function filter_valid(string $entry, Constraint ...$constraints) : Transformer
    {
        return new FilterRowsTransformer(new Opposite(new ValidValue($entry, new ValidValue\SymfonyValidator($constraints))));
    }

    public static function keep(string ...$entrys) : Transformer
    {
        return new KeepEntriesTransformer(...$entrys);
    }

    /**
     * @param string $object_name
     * @param string $method
     * @param string $entry_name
     * @param array<mixed> $parameters
     */
    public static function object_method(string $object_name, string $method, string $entry_name = 'entry', array $parameters = []) : Transformer
    {
        return new Transformer\ObjectMethodTransformer($object_name, $method, $entry_name, $parameters);
    }

    public static function remove(string ...$entrys) : Transformer
    {
        return new Transformer\RemoveEntriesTransformer(...$entrys);
    }

    public static function rename(string $from, string $to) : Transformer
    {
        return new RenameEntriesTransformer(new EntryRename($from, $to));
    }

    /**
     * @param string[] $string_columns
     * @param string $glue
     * @param string $entry_name
     */
    public static function string_concat(array $string_columns, string $glue = '', string $entry_name = 'entry') : Transformer
    {
        return new Transformer\StringConcatTransformer($string_columns, $glue, $entry_name);
    }

    public static function to_array_from_json(string ...$entrys) : Transformer
    {
        return new CastTransformer(CastJsonToArray::nullable($entrys));
    }

    public static function to_array_from_object(string $entry) : Transformer
    {
        if (!\class_exists('Laminas\Hydrator\ReflectionHydrator')) {
            throw new RuntimeException("Laminas\Hydrator\ReflectionHydrator class not found, please install it using 'composer require laminas/laminas-hydrator'");
        }

        return new Transformer\ObjectToArrayTransformer(new ReflectionHydrator(), $entry);
    }

    /**
     * @param string[] $entrys
     * @param ?string $timezone
     * @param ?string $to_timezone
     */
    public static function to_datetime(array $entrys, ?string $timezone = null, ?string $to_timezone = null) : Transformer
    {
        return new CastTransformer(CastToDateTime::nullable($entrys, $timezone, $to_timezone));
    }

    /**
     * @param array<string> $entrys
     * @param null|string $tz
     * @param null|string $toTz
     */
    public static function to_datetime_from_string(array $entrys, ?string $tz = null, ?string $toTz = null) : Transformer
    {
        return new CastTransformer(new Transformer\Cast\CastEntries($entrys, new StringToDateTimeEntryCaster($tz, $toTz), true));
    }

    public static function to_integer(string ...$entrys) : Transformer
    {
        return new CastTransformer(CastToInteger::nullable($entrys));
    }

    public static function to_json(string ...$entrys) : Transformer
    {
        return new CastTransformer(CastToJson::nullable($entrys));
    }

    public static function to_null_from_null_string(string ...$entrys) : Transformer
    {
        return new Transformer\NullStringIntoNullEntryTransformer(...$entrys);
    }

    public static function to_string(string ...$entrys) : Transformer
    {
        return new CastTransformer(CastToString::nullable($entrys));
    }

    /**
     * @param array<string> $entrys
     * @param string $format
     */
    public static function to_string_from_datetime(array $entrys, string $format) : Transformer
    {
        return new CastTransformer(new Transformer\Cast\CastEntries($entrys, new DateTimeToStringEntryCaster($format), true));
    }

    public static function transform_if(Transformer\Condition\RowCondition $condition, Transformer $transformer) : Transformer
    {
        return new Transformer\ConditionalTransformer($condition, $transformer);
    }
}
