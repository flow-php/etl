<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Row\Entry;

use Flow\ETL\DSL\Entry;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\Row\Entry\ListEntry;
use Flow\ETL\Row\Entry\TypedCollection\Type;
use Flow\ETL\Row\Schema\Definition;
use PHPUnit\Framework\TestCase;

final class ListEntryTest extends TestCase
{
    public function test_create_with_empty_name() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entry name cannot be empty');

        Entry::list_of_string('', ['one', 'two', 'three']);
    }

    public function test_creating_boolean_list_from_wrong_value_types() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected list of boolean got: string, boolean');

        new ListEntry('list', Type::boolean, ['string', false]);
    }

    public function test_creating_datetime_list_from_wrong_value_types() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected list of dateTime got: string, object');

        new ListEntry('list', Type::dateTime, ['string', new \DateTimeImmutable()]);
    }

    public function test_creating_float_list_from_wrong_value_types() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected list of float got: string, float');

        new ListEntry('list', Type::float, ['string', 1.3]);
    }

    public function test_creating_integer_list_from_wrong_value_types() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected list of integer got: string, integer');

        new ListEntry('list', Type::integer, ['string', 1]);
    }

    public function test_creating_string_list_from_wrong_value_types() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected list of string got: string, integer');

        new ListEntry('list', Type::string, ['string', 1]);
    }

    public function test_definition() : void
    {
        $this->assertEquals(
            Definition::list('strings', Type::string, false),
            Entry::list_of_string('strings', ['one', 'two', 'three'])->definition()
        );
    }

    public function test_is_equal() : void
    {
        $this->assertTrue(
            Entry::list_of_string('strings', ['one', 'two', 'three'])
                ->isEqual(Entry::list_of_string('strings', ['one', 'two', 'three']))
        );
        $this->assertFalse(
            Entry::list_of_string('strings', ['one', 'two', 'three'])
                ->isEqual(Entry::list_of_int('strings', [1, 2, 3]))
        );
        $this->assertTrue(
            Entry::list_of_string('strings', ['two', 'one', 'three'])
                ->isEqual(Entry::list_of_string('strings', ['one', 'two', 'three']))
        );
    }

    public function test_map() : void
    {
        $this->assertEquals(
            Entry::list_of_string('strings', ['one, two, three']),
            Entry::list_of_string('strings', ['one', 'two', 'three'])->map(fn (array $value) => [\implode(', ', $value)])
        );
    }

    public function test_rename() : void
    {
        $this->assertEquals(
            Entry::list_of_string('new_name', ['one', 'two', 'three']),
            Entry::list_of_string('strings', ['one', 'two', 'three'])->rename('new_name')
        );
    }

    public function test_to_string() : void
    {
        $this->assertEquals(
            '["one","two","three"]',
            Entry::list_of_string('strings', ['one', 'two', 'three'])->toString()
        );
    }

    public function test_to_string_date_time() : void
    {
        $this->assertEquals(
            '[{"date":"2021-01-01 00:00:00.000000","timezone_type":3,"timezone":"UTC"}]',
            Entry::list_of_datetime('strings', [new \DateTimeImmutable('2021-01-01 00:00:00')])->toString()
        );
    }

    public function test_type() : void
    {
        $this->assertEquals(
            Type::string,
            Entry::list_of_string('strings', ['one', 'two', 'three'])->type()
        );
    }

    public function test_value() : void
    {
        $this->assertEquals(
            ['one', 'two', 'three'],
            Entry::list_of_string('strings', ['one', 'two', 'three'])->value()
        );
    }
}
