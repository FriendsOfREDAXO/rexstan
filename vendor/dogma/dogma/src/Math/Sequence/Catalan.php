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
use Dogma\StaticClassMixin;
use Dogma\ValueOutOfBoundsException;
use function array_search;

/**
 * A000108
 */
class Catalan implements Sequence
{
    use StaticClassMixin;

    /** @var array<int, int> */
    private static $cache = [
        1 => 1, 2, 5, 14, 42, 132, 429, 1430, 4862, 16796, 58786, 208012, 742900, 2674440, 9694845, 35357670, 129644790,
        477638700, 1767263190, 6564120420, 24466267020, 91482563640, 343059613650, 1289904147324, 4861946401452,
        18367353072152, 69533550916004, 263747951750360, 1002242216651368, 3814986502092304, 14544636039226909,
        55534064877048198, 212336130412243110, 812944042149730764, 3116285494907301262, // next is > PHP_INT_MAX
    ];

    public static function getNth(int $position): int
    {
        if ($position > 35) {
            throw new ValueOutOfBoundsException($position, 'catalan(n)');
        }

        return self::$cache[$position]
            // actual calculation. never called because of cache
            ?? IntCalc::binomialCoefficient(2 * $position, $position) / ($position + 1);
    }

    public static function getPosition(int $number): ?int
    {
        return array_search($number, self::$cache, true) ?: null;
    }

}
