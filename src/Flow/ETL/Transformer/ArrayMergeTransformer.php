<?php

declare(strict_types=1);

namespace Flow\ETL\Transformer;

use Flow\ETL\Exception\RuntimeException;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

/**
 * @implements Transformer<array{array_entries: array<string>, new_entry_name: string}>
 * @psalm-immutable
 */
final class ArrayMergeTransformer implements Transformer
{
    /**
     * @var array<string>
     */
    private array $arrayEntries;

    private string $newEntryName;

    /**
     * @param array<string> $arrayEntries
     * @param string $newEntryName
     */
    public function __construct(array $arrayEntries, string $newEntryName = 'merged')
    {
        $this->arrayEntries = $arrayEntries;
        $this->newEntryName = $newEntryName;
    }

    public function __serialize() : array
    {
        return [
            'array_entries' => $this->arrayEntries,
            'new_entry_name' => $this->newEntryName,
        ];
    }

    public function __unserialize(array $data) : void
    {
        $this->arrayEntries = $data['array_entries'];
        $this->newEntryName = $data['new_entry_name'];
    }

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     */
    public function transform(Rows $rows) : Rows
    {
        /**
         * @psalm-var pure-callable(Row $row) : Row $transformer
         */
        $transformer = function (Row $row) : Row {
            $entryValues = [];

            foreach ($this->arrayEntries as $entryName) {
                if (!$row->entries()->has($entryName)) {
                    throw new RuntimeException("\"{$entryName}\" not found");
                }

                if (!$row->entries()->get($entryName) instanceof Row\Entry\ArrayEntry) {
                    throw new RuntimeException("\"{$entryName}\" is not ArrayEntry");
                }

                $entryValues[] = $row->get($entryName)->value();
            }

            return $row->add(new Row\Entry\ArrayEntry(
                $this->newEntryName,
                /** @phpstan-ignore-next-line */
                \array_merge(...$entryValues)
            ));
        };

        return $rows->map($transformer);
    }
}
