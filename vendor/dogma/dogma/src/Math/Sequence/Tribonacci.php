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

use Dogma\Math\Constant;
use Dogma\StaticClassMixin;
use Dogma\ValueOutOfBoundsException;
use function intval;
use function round;

/**
 * A000073
 */
class Tribonacci implements Sequence
{
    use StaticClassMixin;

    /** @var int[] */
    private static $cache = [
        0, 0, 1, 1, 2, 4, 7, 13, 24, 44, 81, 149, 274, 504, 927, 1705, 3136, 5768, 10609, 19513, 35890, 66012, 121415,
        223317, 410744, 755476, 1389537, 2555757, 4700770, 8646064, 15902591, 29249425, 53798080, 98950096, 181997601,
        334745777, 615693474, 1132436852, 2082876103, 3831006429, 7046319384, 12960201916, 23837527729, 43844049029,
        80641778674, 148323355432, 272809183135, 501774317241, 922906855808, 1697490356184, 3122171529233,
        5742568741225, 10562230626642, 19426970897100, 35731770264967, 65720971788709, 120879712950776, 222332455004452,
        408933139743938, 752145307699166, 1383410902447556, 2544489349890660, 4680045560037383, 8607945812375600,
        15832480722303644, 29120472094716628, 53560898629395872, 98513851446416160, 181195222170528672,
        333269972246340736, 612979045863285504, 1127444240280155008, 2073693258389781248, 3814116544533222400,
        7015254043203159040,
    ];

    public static function getNth(int $position): int
    {
        static $a = 1 / (4 * Constant::TRIBONACCI - Constant::TRIBONACCI ** 2 - 1);

        return intval(round(Constant::TRIBONACCI ** $position * $a));
    }

    public static function getPosition(int $number): ?int
    {
        if ($number > 92 || $number < 1) {
            throw new ValueOutOfBoundsException($number, "tribonacci($number)");
        }

        return self::$cache[$number];
    }

}
