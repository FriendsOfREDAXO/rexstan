<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Enum;

use Dogma\Cls;
use Dogma\Dumpable;
use Dogma\Equalable;
use Dogma\InvalidValueException;
use Dogma\Obj;
use function array_search;
use function in_array;
use function sprintf;

/**
 * Base class for enums with string values.
 *
 * @see about.md to find out how enum inheritance works.
 */
abstract class StringEnum implements Enum, Dumpable
{
    use EnumSetMixin;

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var string */
    private $value;

    final public function __construct(string $value)
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        if (!static::validateValue($value)) {
            throw new InvalidValueException($value, $class);
        }

        $this->value = $value;
    }

    /**
     * @return static
     */
    final public static function get(string $value): self
    {
        return new static($value);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%s %s #%s)',
            Cls::short(static::class),
            $this->value,
            $this->getConstantName(),
            Obj::dumpHash($this)
        );
    }

    /**
     * Validates given value. Can also normalize the value, if needed.
     *
     * @return bool
     */
    public static function validateValue(string &$value): bool
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return in_array($value, self::$availableValues[$class], true);
    }

    final public function getValue(): string
    {
        return $this->value;
    }

    final public function getConstantName(): string
    {
        /** @var string $result */
        $result = array_search($this->value, self::$availableValues[static::class], true);

        return $result;
    }

    final public static function isValid(string $value): bool
    {
        return self::validateValue($value);
    }

    /**
     * @return string[]
     */
    final public static function getAllowedValues(): array
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        return self::$availableValues[$class];
    }

    /**
     * @param StringEnum $other
     * @return bool
     */
    final public function equals(Equalable $other): bool
    {
        $this->checkCompatibility($other);

        return $this->value === $other->value;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $value
     * @return bool
     */
    final public function equalsValue($value): bool
    {
        return $value === $this->value;
    }

}
