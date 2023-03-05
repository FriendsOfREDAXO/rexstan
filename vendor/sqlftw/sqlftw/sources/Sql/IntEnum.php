<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use Dogma\Arr;
use LogicException;
use ReflectionClass;
use SqlFtw\Formatter\Formatter;
use function array_search;
use function get_class;
use function is_int;

abstract class IntEnum implements SqlSerializable
{

    /** @var array<class-string, array<string, int>> ($class => ($constName => $value)) */
    private static array $availableValues = [];

    private int $value;

    final public function __construct(int $value)
    {
        $class = static::class;
        if (!isset(self::$availableValues[$class])) {
            self::init($class);
        }

        if (!static::validateValue($value)) {
            throw new InvalidEnumValueException($value, $class);
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getConstantName(): string
    {
        return array_search($this->value, self::$availableValues[static::class], true); // @phpstan-ignore-line guaranteed to exist
    }

    /**
     * @param static $other
     */
    public function equals(IntEnum $other): bool
    {
        if (get_class($other) !== static::class) {
            throw new LogicException('Comparing incompatible IntEnum types.');
        }

        return $this->value === $other->value;
    }

    public function equalsValue(int $value): bool
    {
        $other = new static($value);

        return $this->value === $other->value;
    }

    public function equalsAnyValue(int ...$values): bool
    {
        foreach ($values as $value) {
            if ($this->equalsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function serialize(Formatter $formatter): string
    {
        return (string) $this->getValue();
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param class-string $class
     */
    private static function init(string $class): void
    {
        $ref = new ReflectionClass($class);
        self::$availableValues[$class] = [];
        foreach ($ref->getReflectionConstants() as $constant) {
            if (!$constant->isPublic()) {
                continue;
            }
            $value = $constant->getValue();
            if (!is_int($value)) {
                throw new LogicException('Constants in IntEnum must be integers.');
            }
            self::$availableValues[$class][$constant->getName()] = $value;
        }
    }

    /**
     * Normalizes and validates given value
     */
    public static function validateValue(int &$value): bool
    {
        $class = static::class;
        if (!isset(self::$availableValues[$class])) {
            self::init($class);
        }

        return Arr::contains(self::$availableValues[$class], $value);
    }

    public static function isValidValue(int $value): bool
    {
        return static::validateValue($value);
    }

    public static function checkValue(int $value): void
    {
        if (!self::validateValue($value)) {
            throw new InvalidEnumValueException($value, static::class);
        }
    }

    /**
     * @return array<string, int>
     */
    public static function getAllowedValues(): array
    {
        $class = static::class;
        if (!isset(self::$availableValues[$class])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

}
