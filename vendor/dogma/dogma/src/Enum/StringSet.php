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
use Dogma\Obj;
use function array_diff;
use function array_intersect;
use function array_merge;
use function array_search;
use function array_unique;
use function count;
use function explode;
use function implode;
use function in_array;
use function sort;
use function sprintf;

/**
 * Base class for sets of string values
 *
 * @see about.md to find out how enum inheritance works.
 */
abstract class StringSet implements Set, Dumpable
{
    use EnumSetMixin;

    /** @var mixed[][] ($class => ($constName => $value)) */
    private static $availableValues = [];

    /** @var string[] */
    private $values;

    /**
     * @param string[] $values
     */
    final public function __construct(array $values)
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        $values = array_unique($values);
        sort($values);

        self::checkValues($values);

        $this->values = $values;
    }

    /**
     * @return static
     */
    final public static function get(string ...$values): self
    {
        return new static($values);
    }

    /**
     * @return static
     */
    final public static function getByValue(string $value): self
    {
        return new static(explode(',', $value));
    }

    /**
     * @return static
     */
    public static function all(): self
    {
        return new static(self::getAllowedValues());
    }

    /**
     * @return static
     */
    public function invert(): self
    {
        return new static(array_diff(self::getAllowedValues(), $this->values));
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        $names = [];
        foreach ($this->getConstantNames() as $value => $name) {
            $names[] = $value . ' ' . $name;
        }

        return $this->values !== []
            ? sprintf(
                "%s(%s #%s)\n[\n    %s\n]",
                Cls::short(static::class),
                count($this->values),
                Obj::dumpHash($this),
                implode("\n", $names)
            )
            : sprintf(
                '%s(%s #%s)',
                Cls::short(static::class),
                count($this->values),
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

    public function getValue(): string
    {
        return implode(',', $this->values);
    }

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return string[]
     */
    public function getConstantNames(): array
    {
        $names = [];
        foreach ($this->values as $value) {
            $names[$value] = array_search($value, self::$availableValues[static::class], true);
        }

        return $names;
    }

    // comparing sets --------------------------------------------------------------------------------------------------

    /**
     * @param StringSet $other
     * @return bool
     */
    final public function equals(Equalable $other): bool
    {
        $this->checkCompatibility($other);

        return $this->values === $other->values;
    }

    public function contains(self $other): bool
    {
        $this->checkCompatibility($other);

        return count(array_intersect($this->values, $other->values)) === count($other->values);
    }

    public function intersects(self $other): bool
    {
        $this->checkCompatibility($other);

        return array_intersect($this->values, $other->values) !== [];
    }

    public function intersect(self $other): self
    {
        $this->checkCompatibility($other);

        return new static(array_intersect($this->values, $other->values));
    }

    public function union(self $other): self
    {
        $this->checkCompatibility($other);

        return new static(array_merge($this->values, $other->values));
    }

    public function subtract(self $other): self
    {
        $this->checkCompatibility($other);

        return new static(array_diff($this->values, $other->values));
    }

    public function difference(self $other): self
    {
        $this->checkCompatibility($other);

        return new static(array_merge(
            array_intersect($this->invert()->values, $other->values),
            array_intersect($this->values, $other->invert()->values)
        ));
    }

    // comparing values ------------------------------------------------------------------------------------------------

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $value
     * @return bool
     */
    public function equalsValue($value): bool
    {
        return $value === $this->getValue();
    }

    public function containsAll(string ...$values): bool
    {
        self::checkValues($values);

        return count(array_intersect($this->values, $values)) === count($values);
    }

    public function containsAny(string ...$values): bool
    {
        self::checkValues($values);

        return array_intersect($this->values, $values) !== [];
    }

    /**
     * @return static
     */
    public function filter(string ...$allowedValues): self
    {
        return new static(array_intersect($this->values, $allowedValues));
    }

    /**
     * @return static
     */
    public function add(string ...$addValues): self
    {
        return new static(array_merge($this->values, $addValues));
    }

    /**
     * @return static
     */
    public function remove(string ...$removeValues): self
    {
        self::checkValues($removeValues);

        return new static(array_diff($this->values, $removeValues));
    }

    public function xor(string ...$compareValues): self
    {
        $invertedValues = array_diff(self::getAllowedValues(), $compareValues);

        return new static(array_merge(
            array_intersect($this->invert()->values, $compareValues),
            array_intersect($this->values, $invertedValues)
        ));
    }

}
