<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Entry;

use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\Row\Entry;

/**
 * @psalm-immutable
 */
final class XMLEntry implements Entry
{
    private string $key;

    private string $name;

    private \DOMDocument $value;

    public function __construct(string $name, \DOMDocument $value)
    {
        if (!\strlen($name)) {
            throw InvalidArgumentException::because('Entry name cannot be empty');
        }

        $this->key = \mb_strtolower($name);
        $this->name = $name;
        $this->value = $value;
    }

    public static function fromString(string $name, string $value, string $version = '1.0', string $encoding = '') : self
    {
        $dom = new \DOMDocument($version, $encoding);
        $dom->loadXML($value);

        return new self($name, $dom);
    }

    public function name() : string
    {
        return $this->name;
    }

    public function value() : \DOMDocument
    {
        return $this->value;
    }

    public function is(string $name) : bool
    {
        return $this->key === \mb_strtolower($name);
    }

    public function rename(string $name) : Entry
    {
        return new self($name, $this->value);
    }

    /**
     * @psalm-suppress MixedArgument
     */
    public function map(callable $mapper) : Entry
    {
        return new self($this->name, $mapper($this->value()));
    }

    public function isEqual(Entry $entry) : bool
    {
        /**
         * @psalm-suppress ImpureMethodCall
         */
        return $this->is($entry->name()) && $entry instanceof self
            && ($this->value()->saveXML() === $entry->value()->saveXML());
    }
}
