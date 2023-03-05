<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Classes.ClassMemberSpacing

namespace Dogma\Math;

use Dogma\Check;
use Dogma\Math\Sequence\Prime;
use Dogma\StaticClassMixin;
use const PHP_INT_MAX;
use function abs;
use function array_product;
use function ceil;
use function floor;
use function min;
use function range;

class IntCalc
{
    use StaticClassMixin;

    // rounding & mapping ----------------------------------------------------------------------------------------------

    public static function roundTo(int $n, int $multiple): int
    {
        $up = self::roundUpTo($n, $multiple);
        $down = self::roundDownTo($n, $multiple);

        return abs($up - $n) > abs($n - $down) ? $down : $up;
    }

    public static function roundDownTo(int $n, int $multiple): int
    {
        $multiple = abs($multiple);

        return (int) (floor($n / $multiple) * $multiple);
    }

    public static function roundUpTo(int $n, int $multiple): int
    {
        $multiple = abs($multiple);

        return (int) (ceil($n / $multiple) * $multiple);
    }

    /**
     * Maps number from range 0.0 - 1.0 to integers 0 to $max with same probability for each integer
     *
     * @param float $n (range 0..1)
     * @return int
     */
    public static function mapTo(float $n, int $max): int
    {
        return (int) min(floor($n * ($max + 1)), $max);
    }

    // factorization ---------------------------------------------------------------------------------------------------

    /**
     * @return int|float
     */
    public static function factorial(int $n)
    {
        $sign = $n < 0 ? -1 : 1;
        $n = $sign * $n;

        return $sign * ($n > 1 ? array_product(range(2, $n)) : 1);
    }

    /**
     * @return int[]
     */
    public static function factorize(int $n): array
    {
        Check::range($n, 1);
        if ($n === 1) {
            return [1];
        }

        $possibleFactors = Prime::getUntil($n);

        $factors = [];
        foreach ($possibleFactors as $factor) {
            while (($n % $factor) === 0) {
                $factors[] = $factor;
                $n /= $factor;
            }
        }

        return $factors;
    }

    public static function greatestCommonDivider(int $a, int $b): int
    {
        $next = $a % $b;

        return $next === 0 ? $b : self::greatestCommonDivider($b, $next);
    }

    public static function leastCommonMultiple(int $a, int $b): int
    {
        return $a * ($b / self::greatestCommonDivider($a, $b));
    }

    public static function binomialCoefficient(int $n, int $k): int
    {
        $result = 1;

        // since C(n, k) = C(n, n-k)
        if ($k > $n - $k) {
            $k = $n - $k;
        }

        // calculate value of [n*(n-1)*---*(n-k+1)] / [k*(k-1)*---*1]
        for ($i = 0; $i < $k; ++$i) {
            $result *= ($n - $i);
            $result /= ($i + 1);
        }

        /** @var int $result */
        $result = $result;

        return $result;
    }

    // bit operations --------------------------------------------------------------------------------------------------

    /**
     * @return int[]
     */
    public static function binaryComponents(int $n): array
    {
        $components = [];
        $e = 0;
        do {
            $c = 1 << $e;
            if (($n & $c) !== 0) {
                $components[] = $c;
            }
        } while ($e++ < 64);

        return $components;
    }

    public static function copyBits(int $from, int $to, int $mask): int
    {
        return ($from & $mask) | ($to & ~$mask);
    }

    public static function swapBits(int $n, int $pos1, int $pos2): int
    {
        $x = (($n >> $pos1) ^ ($n >> $pos2)) & 1;

        return $x === 0 ? $n : $n ^ ($x << $pos1) ^ ($x << $pos2);
    }

    public static function countOnes(int $n): int
    {
        $count = 0;
        if ($n < 0) {
            $count = 1;
            $n = $n & PHP_INT_MAX;
        }

        while ($n !== 0) {
            $n &= $n - 1;
            $count++;
        }

        return $count;
    }

    public static function countBlocks(int $n): int
    {
        return ($n < 0) + ($n & 1) + self::countOnes($n ^ ($n >> 1)) >> 1;
    }

    public static function flipTrailingZeros(int $n): int
    {
        return ($n - 1) | $n;
    }

    public static function leastSignificantBit(int $n): int
    {
        return $n & -$n;
    }

    /**
     * @see BSF
     */
    public static function leastSignificantBitIndex(int $n): int
    {
        if ($n === 0) {
            return -1;
        }
        $x = $n & -$n;
        $i = 0;
        if ($x & 0xffff0000) {
            $i += 16;
        }
        if ($x & 0xff00ff00) {
            $i += 8;
        }
        if ($x & 0xf0f0f0f0) {
            $i += 4;
        }
        if ($x & 0xcccccccc) {
            $i += 2;
        }
        if ($x & 0xaaaaaaaa) {
            $i += 1;
        }

        return $i;
    }

    /**
     * @see CoLex
     */
    public static function permutateBits(int $n): int
    {
        $x = $n | ($n - 1);

        return ($x + 1) | ((~$x & -(~$x)) - 1) >> (self::leastSignificantBitIndex($n) + 1);
    }

}
