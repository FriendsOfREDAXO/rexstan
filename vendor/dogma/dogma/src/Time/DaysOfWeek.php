<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Enum\IntSet;
use function array_filter;
use function intval;

class DaysOfWeek extends IntSet
{

    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 4;
    public const THURSDAY = 8;
    public const FRIDAY = 16;
    public const SATURDAY = 32;
    public const SUNDAY = 64;

    public static function createFromDays(DayOfWeek ...$days): self
    {
        $values = 0;
        foreach ($days as $day) {
            $value = 2 ** ($day->getValue() - 1);
            $values |= $value;
        }

        return self::get($values);
    }

    public static function allDays(): self
    {
        return self::get(127);
    }

    public static function workDays(): self
    {
        return self::get(self::SATURDAY - 1);
    }

    public static function weekend(): self
    {
        return self::get(self::SATURDAY | self::SUNDAY);
    }

    /**
     * @return DayOfWeek[]
     */
    public function toDays(): array
    {
        $days = [];
        foreach (DayOfWeek::getAllowedValues() as $value) {
            if ($this->containsAll(2 ** ($value - 1))) {
                $days[] = DayOfWeek::get($value);
            }
        }

        return $days;
    }

    public function containsDay(DayOfWeek $day): bool
    {
        return $this->containsAll(2 ** ($day->getValue() - 1));
    }

    public function matchesDay(DateOrDateTime $date): bool
    {
        $day = 2 ** (intval($date->format('N')) - 1);

        return $this->containsAll($day);
    }

    /**
     * @param DateOrDateTime[] $days
     * @return DateOrDateTime[]
     */
    public function filterDays(array $days): array
    {
        return array_filter($days, function (DateOrDateTime $day) {
            return $this->matchesDay($day);
        });
    }

}
