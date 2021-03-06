<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Row;

use Flow\ETL\Exception\RuntimeException;
use Flow\ETL\Row\Entries;
use Flow\ETL\Row\Entry\BooleanEntry;
use Flow\ETL\Row\Entry\DateTimeEntry;
use Flow\ETL\Row\Entry\IntegerEntry;
use Flow\ETL\Row\Entry\NullEntry;
use Flow\ETL\Row\Entry\StringEntry;
use Flow\ETL\Row\Entry\StructureEntry;
use PHPUnit\Framework\TestCase;

final class EntriesTest extends TestCase
{
    public function test_prevents_from_creating_collection_with_duplicate_entry_names() : void
    {
        $this->expectExceptionMessage('Entry names must be unique');

        new Entries(
            new StringEntry('entry-name', 'just a string'),
            new IntegerEntry('entry-name', 100)
        );
    }

    public function test_adds_entry() : void
    {
        $newEntry = new StringEntry('entry-name', 'new string entry');
        $entries = new Entries(
            new IntegerEntry('integer-entry', 100)
        );
        $this->assertFalse($entries->has('entry-name'));

        $entries = $entries->add($newEntry);

        $this->assertTrue($entries->has('entry-name'));
        $this->assertEquals($newEntry, $entries->get('entry-name'));
    }

    public function test_prevents_from_adding_entry_with_the_same_name() : void
    {
        $entries = new Entries(
            new IntegerEntry('entry-name', 100)
        );

        $this->expectExceptionMessage('Entry "entry-name" already exist');

        $entries->add(new StringEntry('entry-name', 'just a string'));
    }

    public function test_removes_entry() : void
    {
        $entries = new Entries(
            $integerEntry = new IntegerEntry('integer-entry', 100),
            new StringEntry('string-entry', 'just a string'),
            $booleanEntry = new BooleanEntry('boolean-entry', true)
        );

        $this->assertEquals(new Entries($integerEntry, $booleanEntry), $entries->remove('string-entry'));
    }

    public function test_assert_if_entry_exists_when_removing_entry() : void
    {
        $entries = new Entries(
            new IntegerEntry('integer-entry', 100),
            new StringEntry('string-entry', 'just a string'),
        );

        $this->expectExceptionMessage('Entry "non-existing-entry" does not exist');

        $entries->remove('non-existing-entry');
    }

    public function test_adds_entry_when_it_does_not_exist() : void
    {
        $stringEntry = new StringEntry('string-entry', 'just a string');
        $entries = new Entries(
            $integerEntry = new IntegerEntry('integer-entry', 100),
            $booleanEntry = new BooleanEntry('boolean-entry', true)
        );

        $entries = $entries->set($stringEntry);

        $this->assertEquals(new Entries($integerEntry, $booleanEntry, $stringEntry), $entries);
    }

    public function test_overwrites_entry_when_it_exists() : void
    {
        $stringEntry = new StringEntry('entry-name', 'just a string');
        $entries = new Entries(
            new IntegerEntry('entry-name', 100),
            $booleanEntry = new BooleanEntry('boolean-entry', true)
        );

        $entries = $entries->set($stringEntry);

        $this->assertEquals(new Entries($booleanEntry, $stringEntry), $entries);
    }

    public function test_prevents_from_getting_unknown_entry() : void
    {
        $entries = new Entries();

        $this->expectExceptionMessage('Entry "unknown" does not exist');

        $entries->get('unknown');
    }

    public function test_transforms_collection_to_array() : void
    {
        $entries = new Entries(
            new IntegerEntry('id', 1234),
            new BooleanEntry('deleted', false),
            new DateTimeEntry('created-at', $createdAt = new \DateTimeImmutable('2020-07-13 15:00')),
            new NullEntry('phase'),
            new StructureEntry(
                'items',
                new IntegerEntry('item-id', 1),
                new StringEntry('name', 'one'),
                new IntegerEntry('item-id', 2),
                new StringEntry('name', 'two'),
                new IntegerEntry('item-id', 3),
                new StringEntry('name', 'three')
            )
        );

        $this->assertEquals(
            [
                'id' => 1234,
                'deleted' => false,
                'created-at' => $createdAt,
                'phase' => null,
                'items' => [
                    ['item-id' => 1],
                    ['name' => 'one'],
                    ['item-id' => 2],
                    ['name' => 'two'],
                    ['item-id' => 3],
                    ['name' => 'three'],
                ],
            ],
            $entries->toArray()
        );
    }

    public function test_sorts_entries_by_name() : void
    {
        $entries = new Entries(
            $id = new IntegerEntry('id', 1234),
            $deleted = new BooleanEntry('deleted', false),
            $createdAt = new DateTimeEntry('created-at', new \DateTimeImmutable('2020-07-13 15:00')),
            $phase = new NullEntry('phase'),
            $items = new StructureEntry(
                'items',
                new IntegerEntry('item-id', 1),
                new StringEntry('name', 'one'),
                new IntegerEntry('item-id', 2),
                new StringEntry('name', 'two'),
                new IntegerEntry('item-id', 3),
                new StringEntry('name', 'three')
            )
        );

        $sorted = $entries->sort();

        $this->assertEquals(
            new Entries(
                $createdAt = new DateTimeEntry('created-at', new \DateTimeImmutable('2020-07-13 15:00')),
                $deleted = new BooleanEntry('deleted', false),
                $id = new IntegerEntry('id', 1234),
                $items = new StructureEntry(
                    'items',
                    new IntegerEntry('item-id', 1),
                    new StringEntry('name', 'one'),
                    new IntegerEntry('item-id', 2),
                    new StringEntry('name', 'two'),
                    new IntegerEntry('item-id', 3),
                    new StringEntry('name', 'three')
                ),
                $phase = new NullEntry('phase')
            ),
            $sorted
        );
    }

    public function test_array_access_get() : void
    {
        $entries = new Entries(new IntegerEntry('id', 1), new StringEntry('name', 'John'));

        $this->assertSame(1, $entries['id']->value());
        $this->assertSame('John', $entries['name']->value());
    }

    public function test_array_access_exists() : void
    {
        $entries = new Entries(new IntegerEntry('id', 1), new StringEntry('name', 'John'));

        $this->assertTrue(isset($entries['id']));
        $this->assertFalse(isset($entries['test']));
    }

    public function test_array_access_set() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('In order to add new rows use Entries::add(Entry $entry) : self');
        $entries = new Entries();
        $entries['id'] = new IntegerEntry('id', 1);
    }

    public function test_array_access_unset() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('In order to add new rows use Entries::remove(string $name) : self');
        $entries = new Entries(new IntegerEntry('id', 1));
        unset($entries['id']);
    }
}
