<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\IntersectResult;
use Dogma\StaticClassMixin;

class IntervalCalc
{
    use StaticClassMixin;

    public static function compareIntersects(int $aStart, int $aEnd, int $bStart, int $bEnd): int
    {
        if ($aStart === $bStart) {
            if ($aEnd === $bEnd) {
                return IntersectResult::SAME;
            } elseif ($aEnd < $bEnd) {
                return IntersectResult::FITS_TO_START;
            } else {
                return IntersectResult::EXTENDS_END;
            }
        } elseif ($aStart < $bStart) {
            if ($aEnd === $bStart - 1) {
                return IntersectResult::TOUCHES_START;
            } elseif ($aEnd < $bStart) {
                return IntersectResult::BEFORE_START;
            } elseif ($aEnd === $bEnd) {
                return IntersectResult::EXTENDS_START;
            } elseif ($aEnd < $bEnd) {
                return IntersectResult::INTERSECTS_START;
            } else {
                return IntersectResult::CONTAINS;
            }
        } else {
            if ($aStart === $bEnd + 1) {
                return IntersectResult::TOUCHES_END;
            } elseif ($aStart > $bEnd) {
                return IntersectResult::AFTER_END;
            } elseif ($aEnd === $bEnd) {
                return IntersectResult::FITS_TO_END;
            } elseif ($aEnd < $bEnd) {
                return IntersectResult::IS_CONTAINED;
            } else {
                return IntersectResult::INTERSECTS_END;
            }
        }
    }

}
