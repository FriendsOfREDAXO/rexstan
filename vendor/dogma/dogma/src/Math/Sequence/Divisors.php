<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable Squiz.Arrays.ArrayDeclaration.ValueNoNewline

namespace Dogma\Math\Sequence;

use Dogma\Math\IntCalc;
use Dogma\NotImplementedException;
use Dogma\StaticClassMixin;
use function count;

/**
 * A000005
 */
class Divisors implements Sequence
{
    use StaticClassMixin;

    /** @var int[] */
    public static $cache = [
        1 => 1, 2, 2, 3, 2, 4, 2, 4, 3, 4, 2, 6, 2, 4, 4, 5, 2, 6, 2, 6, 4, 4, 2, 8, 3, 4, 4, 6, 2, 8, 2, 6, 4, 4, 4, 9,
        2, 4, 4, 8, 2, 8, 2, 6, 6, 4, 2, 10, 3, 6, 4, 6, 2, 8, 4, 8, 4, 4, 2, 12, 2, 4, 6, 7, 4, 8, 2, 6, 4, 8, 2, 12,
        2, 4, 6, 6, 4, 8, 2, 10, 5, 4, 2, 12, 4, 4, 4, 8, 2, 12, 4, 6, 4, 4, 4, 12, 2, 6, 6, 9,
    ];

    public static function getNth(int $position): int
    {
        if (!isset(self::$cache[$position])) {
            self::$cache[$position] = count(IntCalc::factorize($position)) + 1;
        }

        return self::$cache[$position];
    }

    public static function getPosition(int $number): ?int
    {
        throw new NotImplementedException('');
    }

}
