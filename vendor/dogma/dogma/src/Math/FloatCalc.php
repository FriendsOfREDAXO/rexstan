<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\StaticClassMixin;
use function abs;
use function ceil;
use function floor;

class FloatCalc
{
    use StaticClassMixin;

    public const EPSILON = 0.0000000000001;

    public static function equals(float $first, float $second, float $epsilon = self::EPSILON): bool
    {
        return abs($first - $second) < $epsilon;
    }

    public static function roundTo(float $number, float $multiple): float
    {
        $up = self::roundUpTo($number, $multiple);
        $down = self::roundDownTo($number, $multiple);

        return abs($up - $number) > abs($number - $down) ? $down : $up;
    }

    public static function roundDownTo(float $number, float $multiple): float
    {
        $multiple = abs($multiple);

        return floor($number / $multiple) * $multiple;
    }

    public static function roundUpTo(float $number, float $multiple): float
    {
        $multiple = abs($multiple);

        return ceil($number / $multiple) * $multiple;
    }

}
