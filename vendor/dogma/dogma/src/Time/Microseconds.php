<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\StaticClassMixin;

/**
 * Literally million times better than seconds!
 */
class Microseconds
{
    use StaticClassMixin;

    public const SECOND = 1000000;
    public const MINUTE = Seconds::MINUTE * self::SECOND;
    public const HOUR = Seconds::HOUR * self::SECOND;
    public const DAY = Seconds::DAY * self::SECOND;
    public const WEEK = Seconds::WEEK * self::SECOND;
    public const COMMON_YEAR = Seconds::COMMON_YEAR * self::SECOND;
    public const LEAP_YEAR = Seconds::LEAP_YEAR * self::SECOND;

}
