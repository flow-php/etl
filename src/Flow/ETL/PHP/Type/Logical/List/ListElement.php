<?php declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Logical\List;

use Flow\ETL\PHP\Type\Logical\ListType;
use Flow\ETL\PHP\Type\Logical\MapType;
use Flow\ETL\PHP\Type\Native\ObjectType;
use Flow\ETL\PHP\Type\Native\ScalarType;
use Flow\ETL\PHP\Type\Type;

final class ListElement
{
    private function __construct(private readonly Type $value)
    {
    }

    public static function boolean() : self
    {
        return new self(ScalarType::boolean());
    }

    public static function float() : self
    {
        return new self(ScalarType::float());
    }

    public static function fromType(Type $type) : self
    {
        return new self($type);
    }

    public static function integer() : self
    {
        return new self(ScalarType::integer());
    }

    public static function list(ListType $type) : self
    {
        return new self($type);
    }

    public static function map(MapType $type) : self
    {
        return new self($type);
    }

    /**
     * @param class-string $class
     */
    public static function object(string $class, bool $nullable = false) : self
    {
        return new self(ObjectType::of($class, $nullable));
    }

    public static function string() : self
    {
        return new self(ScalarType::string());
    }

    public function isEqual(mixed $value) : bool
    {
        return $this->value->isEqual($value);
    }

    public function isValid(mixed $value) : bool
    {
        return $this->value->isValid($value);
    }

    public function toString() : string
    {
        return $this->value->toString();
    }

    public function type() : Type
    {
        return $this->value;
    }
}
