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
use function array_merge;
use function strlen;
use function substr;

class Combinatorics
{
    use StaticClassMixin;

    /**
     * Get all combinations of integers, the given integer can be sum of.
     * @return int[][]
     */
    public static function sumFactorize(int $number): array
    {
        if ($number === 1) {
            return [[1]];
        }
        $variants = [[$number]];
        for ($factor = $number - 1; $factor > 0; $factor--) {
            $factors = self::sumFactorize($number - $factor);
            foreach ($factors as $variant) {
                $variants[] = array_merge([$factor], $variant);
            }
        }
        return $variants;
    }

    /**
     * @return string[][]
     */
    public static function getAllSubstringCombinations(string $string): array
    {
        $variants = [];
        foreach (self::sumFactorize(strlen($string)) as $lengths) {
            $substrings = [];
            $offset = 0;
            foreach ($lengths as $length) {
                $substrings[] = substr($string, $offset, $length);
                $offset += $length;
            }
            $variants[] = $substrings;
        }
        return $variants;
    }

}
