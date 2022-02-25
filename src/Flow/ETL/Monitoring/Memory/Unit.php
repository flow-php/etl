<?php

declare(strict_types=1);

namespace Flow\ETL\Monitoring\Memory;

use Flow\ETL\Exception\InvalidArgumentException;

final class Unit
{
    private int $bytes;

    private function __construct(int $bytes)
    {
        $this->bytes = $bytes;
    }

    public static function fromString(string $memoryString) : self
    {
        $limit = \str_replace(' ', '', $memoryString);

        $unit = \substr($limit, -1);

        switch (\strtoupper($unit)) {
            case 'K':
                return self::fromKb((int) \substr($limit, 0, -1));
            case 'M':
                return self::fromMb((int) \substr($limit, 0, -1));
            case 'G':
                return self::fromGb((int) \substr($limit, 0, -1));

            default:
                if (\ctype_digit($limit)) {
                    return self::fromBytes((int) $limit);
                }

                    throw new InvalidArgumentException("Can't extract memory limit in bytes from php ini value: {$limit}");

        }
    }

    public static function fromBytes(int $bytes) : self
    {
        return new self($bytes);
    }

    public static function fromKb(int $kb) : self
    {
        return new self($kb * 1024);
    }

    public static function fromMb(int $mb) : self
    {
        return new self($mb * 1024 * 1024);
    }

    public static function fromGb(int $gb)
    {
        return new self($gb * 1024 * 1024 * 1024);
    }

    public function inBytes() : int
    {
        return $this->bytes;
    }

    public function inKb(int $precision = 2) : float
    {
        return \round($this->bytes / 1024, $precision);
    }

    public function inMb(int $precision = 2) : float
    {
        return \round($this->inKb($precision) / 1024, $precision);
    }

    public function inGb(int $precision = 2) : float
    {
        return \round($this->inMb($precision) / 1024, $precision);
    }

    public function diff(self $unit) : self
    {
        return new self($this->bytes - $unit->bytes);
    }

    public function absolute() : self
    {
        return new self(\abs($this->bytes));
    }

    public function percentage(int $value) : self
    {
        return new self((int) \round(($value / 100) * $this->bytes));
    }
}
