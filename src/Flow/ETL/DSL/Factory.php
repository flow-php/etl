<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Flow\ETL\Factory\CastedRowsFactory;
use Flow\ETL\Row\Entry;
use Flow\ETL\Row\Factory\ArrayRowsFactory;
use Flow\ETL\Row\Factory\NativeEntryFactory;
use Flow\ETL\Row\RowConverter;
use Flow\ETL\Rows;

final class Factory
{
    /**
     * @param string $entryName
     * @param mixed $value
     */
    public static function entry_from_value(string $entryName, $value) : Entry
    {
        return (new NativeEntryFactory())->create($entryName, $value);
    }

    /**
     * @param array<array> $data
     */
    public static function rows_from_array(array $data) : Rows
    {
        return (new ArrayRowsFactory())->create($data);
    }

    /**
     * @param array<array> $data
     */
    public static function rows_from_casted_array(array $data, RowConverter ...$converters) : Rows
    {
        return (new CastedRowsFactory(new ArrayRowsFactory(), ...$converters))->create($data);
    }
}
