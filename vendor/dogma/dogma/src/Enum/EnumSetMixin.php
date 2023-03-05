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
use Dogma\InvalidTypeException;
use Dogma\InvalidValueException;
use Dogma\LogicException;
use Dogma\StrictBehaviorMixin;
use ReflectionClass;
use ReflectionClassConstant;
use function array_diff_key;
use function end;
use function explode;
use function get_class;
use function implode;
use function in_array;
use function sprintf;
use function strpos;

trait EnumSetMixin
{
    use StrictBehaviorMixin;

    public function __toString(): string
    {
        $parts = explode('\\', static::class);

        return sprintf('%s: %s', end($parts), $this->getValue());
    }

    /**
     * Returns case sensitive regular expression for value validation.
     * Only the body or expression without modifiers, delimiters and start/end assertions ('^' and '$').
     */
    public static function getValueRegexp(): string
    {
        return implode('|', self::getAllowedValues());
    }

    /**
     * @param string[]|int[] $values
     */
    final public static function checkValues(array $values): void
    {
        foreach ($values as $value) {
            if (!self::isValid($value)) {
                throw new InvalidValueException($value, static::class);
            }
        }
    }

    /**
     * @return static[]
     */
    final public static function getInstances(): array
    {
        $class = static::class;
        if (empty(self::$availableValues[$class])) {
            self::init($class);
        }

        $instances = [];
        foreach (self::$availableValues[$class] as $identifier => $value) {
            $instances[$identifier] = static::get($value);
        }

        return $instances;
    }

    /**
     * @param class-string $class
     */
    final protected static function init(string $class): void
    {
        static $rootClasses = [
            IntEnum::class,
            StringEnum::class,
            PartialIntEnum::class,
            PartialStringEnum::class,
            IntSet::class,
            StringSet::class,
        ];

        if (isset(self::$availableValues[$class])) {
            return;
        }

        $ref = new ReflectionClass($class);
        /** @var ReflectionClass<Enum|Set> $parent */
        $parent = $ref->getParentClass();
        $parentClass = $parent->getName();
        if (!in_array($parentClass, [IntEnum::class, StringEnum::class, IntSet::class, StringSet::class], true) && $parent->isAbstract()) {
            /** @var ReflectionClass<Enum|Set> $parent */
            $parent = $parent->getParentClass();
            $parentClass = $parent->getName();
        }
        $isDescendant = !in_array($parentClass, $rootClasses, true);

        $parentValues = [];
        if ($isDescendant) {
            self::init($parentClass);
            $parentValues = self::$availableValues[$parentClass];
        }

        $constants = $ref->getReflectionConstants();
        $values = [];
        $removedValues = [];
        foreach ($constants as $constant) {
            if (!$constant->isPublic()) {
                continue;
            }
            /** @var string $constantName */
            $constantName = $constant->getName();

            if (!$isDescendant) {
                $values[$constantName] = $constant->getValue();
                continue;
            }

            $declaring = $constant->getDeclaringClass();
            if ($declaring->getName() !== $class) {
                continue;
            }

            if (!isset($parentValues[$constantName])) {
                throw new LogicException(
                    sprintf('Enum constant %s::%s must be first defined in parent class %s.', $class, $constantName, $parentClass)
                );
            }
            $value = $constant->getValue();
            if ($parentValues[$constantName] !== $value) {
                throw new LogicException(
                    sprintf('Enum constant %s::%s must have the same value as its definition in parent class %s.', $class, $constantName, $parentClass)
                );
            }
            /** @var ReflectionClassConstant $parentConstant */
            $parentConstant = $parent->getReflectionConstant($constantName);
            $removed = strpos($constant->getDocComment() ?: '', '@removed') !== false;
            $parentRemoved = strpos($parentConstant->getDocComment() ?: '', '@removed') !== false;
            if ($removed) {
                $removedValues[$constantName] = $value;
            } elseif ($parentRemoved) {
                throw new LogicException(
                    sprintf('Enum constant %s::%s was marked as removed in parent class %s and cannot be reintroduced.', $class, $constantName, $parentClass)
                );
            } else {
                $values[$constantName] = $value;
            }
        }
        if ($isDescendant && $values === []) {
            $values = array_diff_key($parentValues, $removedValues);
        }

        self::$availableValues[$class] = $values;
    }

    private function checkCompatibility(self $other): void
    {
        $ancestor = Cls::commonRoot(static::class, get_class($other), self::class);
        if ($ancestor === null) {
            throw new InvalidTypeException(static::class, $other);
        }
    }

}
