<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Enum\StringEnum;
use function in_array;

/**
 * Ať běží rychle a na nic se neptá
 * než bude duše má za chvíli mrtvá
 */
class DateTimeUnit extends StringEnum
{

    public const YEAR = 'year';
    public const QUARTER = 'quarter';
    public const MONTH = 'month';
    public const WEEK = 'week';
    public const DAY = 'day';

    public const HOUR = 'hour';
    public const MINUTE = 'minute';
    public const SECOND = 'second';
    public const MILISECOND = 'milisecond';
    public const MICROSECOND = 'microsecond';

    public static function year(): self
    {
        return self::get(self::YEAR);
    }

    public static function quarter(): self
    {
        return self::get(self::QUARTER);
    }

    public static function month(): self
    {
        return self::get(self::MONTH);
    }

    public static function week(): self
    {
        return self::get(self::WEEK);
    }

    public static function day(): self
    {
        return self::get(self::DAY);
    }

    public static function hour(): self
    {
        return self::get(self::HOUR);
    }

    public static function minute(): self
    {
        return self::get(self::MINUTE);
    }

    public static function second(): self
    {
        return self::get(self::SECOND);
    }

    public static function milisecond(): self
    {
        return self::get(self::MILISECOND);
    }

    public static function microsecond(): self
    {
        return self::get(self::MICROSECOND);
    }

    /**
     * @return string[]
     */
    public static function getDateUnits(): array
    {
        return [
            self::YEAR,
            self::QUARTER,
            self::MONTH,
            self::WEEK,
            self::DAY,
        ];
    }

    /**
     * @return string[]
     */
    public static function getTimeUnits(): array
    {
        return [
            self::HOUR,
            self::MINUTE,
            self::SECOND,
            self::MILISECOND,
            self::MICROSECOND,
        ];
    }

    /**
     * @return string[]
     */
    public static function getComparisonFormats(): array
    {
        return [
            self::YEAR => 'Y',
            self::MONTH => 'Ym',
            self::WEEK => 'oW',
            self::DAY => 'Ymd',
            self::HOUR => 'YmdH',
            self::MINUTE => 'YmdHi',
            self::SECOND => 'YmdHis',
            self::MILISECOND => 'YmdHisv',
            self::MICROSECOND => 'YmdHisu',
        ];
    }

    public function isDate(): bool
    {
        return in_array($this->getValue(), self::getDateUnits(), true);
    }

    public function isTime(): bool
    {
        return in_array($this->getValue(), self::getTimeUnits(), true);
    }

    /**
     * Used in DateTime::equalsUpTo()
     * @return string
     */
    public function getComparisonFormat(): string
    {
        return self::getComparisonFormats()[$this->getValue()];
    }

}
