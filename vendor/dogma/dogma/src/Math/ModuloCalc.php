<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\InvalidArgumentException;
use Dogma\Overflow;
use Dogma\StaticClassMixin;
use function abs;
use function array_values;
use function count;
use function in_array;
use function max;
use function min;
use function rsort;
use function sort;

/**
 * Calculations in modular arithmetic.
 */
class ModuloCalc
{
    use StaticClassMixin;

    /**
     * Calculates all differences between given values.
     * @param int[]|float[] $values
     * @return int[]|float[]
     */
    public static function differences(array $values, int $modulus): array
    {
        self::checkValues($values, $modulus);

        sort($values);
        $values[] = $values[0] + $modulus;

        $differences = [];
        $max = count($values) - 2;
        foreach ($values as $i => $value) {
            $differences[] = $values[$i + 1] - $value;
            if ($i === $max) {
                break;
            }
        }

        return $differences;
    }

    /**
     * Rounds value to the closest value from given set.
     * Sets overflow to true when maximal value is picked, but minimal value is returned.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @return int[]|float[] (int|float $result, int $overflow)
     */
    public static function roundTo($value, array $allowedValues, int $modulus): array
    {
        [$downValue, $underflow] = self::roundDownTo($value, $allowedValues, $modulus);
        [$upValue, $overflow] = self::roundUpTo($value, $allowedValues, $modulus);

        $realDownValue = $underflow === Overflow::UNDERFLOW ? $downValue - $modulus : $downValue;
        $realUpValue = $overflow === Overflow::OVERFLOW ? $upValue + $modulus : $upValue;

        if (abs($value - $realDownValue) < abs($value - $realUpValue)) {
            return [$downValue, $underflow];
        } else {
            return [$upValue, $overflow];
        }
    }

    /**
     * Rounds value to first bigger or same value from given set.
     * Sets overflow to true when maximal value is picked, but minimal value is returned.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @return int[]|float[] (int|float $result, int $overflow)
     */
    public static function roundUpTo($value, array $allowedValues, int $modulus): array
    {
        self::checkValues($allowedValues, $modulus);

        sort($allowedValues);
        if (in_array(0, $allowedValues, true)) {
            $allowedValues[] = $modulus;
        }

        $pickedValue = null;
        foreach ($allowedValues as $allowedValue) {
            if ($value <= $allowedValue) {
                $pickedValue = $allowedValue;
                break;
            }
        }
        if ($pickedValue === null) {
            $overflow = Overflow::OVERFLOW;
            $pickedValue = $allowedValues[0];
        } elseif ($pickedValue === $modulus) {
            $overflow = Overflow::OVERFLOW;
            $pickedValue = 0;
        } else {
            $overflow = Overflow::NONE;
        }

        return [$pickedValue, $overflow];
    }

    /**
     * Rounds value up to first smaller or same value from given set.
     * Cannot overflow.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @return int[]|float[] (int|float $result, int $overflow)
     */
    public static function roundDownTo($value, array $allowedValues, int $modulus): array
    {
        self::checkValues($allowedValues, $modulus);

        rsort($allowedValues);
        $pickedValue = null;
        foreach ($allowedValues as $allowedValue) {
            if ($value >= $allowedValue) {
                $pickedValue = $allowedValue;
                break;
            }
        }

        if ($pickedValue === null) {
            $overflow = Overflow::UNDERFLOW;
            $pickedValue = $allowedValues[0];
        } elseif ($pickedValue === $modulus) {
            $overflow = Overflow::UNDERFLOW;
            $pickedValue = 0;
        } else {
            $overflow = Overflow::NONE;
        }

        return [$pickedValue, $overflow];
    }

    /**
     * @param int[] $values
     */
    private static function checkValues(array &$values, int $modulus): void
    {
        if ($values === []) {
            throw new InvalidArgumentException('Values should not be empty.');
        }
        if (max($values) >= $modulus || min($values) < 0) {
            throw new InvalidArgumentException('All values should be smaller than modulus.');
        }
        $values = array_values($values);
    }

}
