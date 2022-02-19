<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

/**
 * @psalm-immutable
 */
final class StringConcatTransformer implements Transformer
{
    /**
     * @var array<string>
     */
    private array $stringEntryNames;

    private string $glue;

    private string $newEntryName;

    /**
     * @param array<string> $stringEntryNames
     * @param string $glue
     * @param string $newEntryName
     */
    public function __construct(array $stringEntryNames, string $glue = ' ', string $newEntryName = 'element')
    {
        $this->stringEntryNames = $stringEntryNames;
        $this->glue = $glue;
        $this->newEntryName = $newEntryName;
    }

    /**
     * @return array{string_entry_names: array<string>, glue: string, new_entry_name: string}
     */
    public function __serialize() : array
    {
        return [
            'string_entry_names' => $this->stringEntryNames,
            'glue' => $this->glue,
            'new_entry_name' => $this->newEntryName,
        ];
    }

    /**
     * @param array{string_entry_names: array<string>, glue: string, new_entry_name: string} $data
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function __unserialize(array $data) : void
    {
        $this->stringEntryNames = $data['string_entry_names'];
        $this->glue = $data['glue'];
        $this->newEntryName = $data['new_entry_name'];
    }

    public function transform(Rows $rows) : Rows
    {
        /**
         * @psalm-var pure-callable(Row $row) : Row $transformer
         */
        $transformer = function (Row $row) : Row {
            $entries = $row->filter(fn (Row\Entry $entry) : bool => \in_array($entry->name(), $this->stringEntryNames, true) && $entry instanceof Row\Entry\StringEntry)->entries();
            /** @var array<string> $values */
            $values = [];

            foreach ($entries->all() as $entry) {
                /** @phpstan-ignore-next-line */
                $values[] = (string) $entry->value();
            }

            return $row->add(
                new Row\Entry\StringEntry(
                    $this->newEntryName,
                    \implode($this->glue, $values)
                )
            );
        };

        return $rows->map($transformer);
    }
}