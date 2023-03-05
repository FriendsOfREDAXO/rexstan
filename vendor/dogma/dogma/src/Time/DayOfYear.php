<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Cls;
use Dogma\Comparable;
use Dogma\Dumpable;
use Dogma\Equalable;
use Dogma\Obj;
use Dogma\Order;
use Dogma\StrictBehaviorMixin;
use function is_string;
use function sprintf;

class DayOfYear implements Equalable, Comparable, Dumpable
{
    use StrictBehaviorMixin;

    public const MIN = '01-01';
    public const MAX = '12-31';

    public const MIN_NUMBER = 1;
    public const MAX_NUMBER = 366;
    public const MAX_DENORMALIZED = self::MAX_NUMBER * 2;

    public const DEFAULT_FORMAT = 'm-d';
    public const DEFAULT_FORMAT_YEAR = 2000; // because it is a leap-year. We do not want February 29th to fallback to March 1st

    /** @var int */
    private $number;

    /** @var int */
    private $month;

    /** @var int */
    private $dayOfMonth;

    /**
     * Receives date string (e.g. "02-29") or number of day in year.
     *
     * This number may differ from the actual order of day in a particular year, because always counts with February 29th:
     * 1 = January 1st
     * ...
     * 59 = February 28th
     * 60 = February 29th
     * 61 = March 1st (even in non-leap years!)
     * ...
     * 366 = December 31st (even in non-leap years!)
     *
     * @param int|string $dateOrDayNumber
     */
    final public function __construct($dateOrDayNumber)
    {
        if (is_string($dateOrDayNumber)) {
            $date = new Date('2000-' . $dateOrDayNumber);
            $dateOrDayNumber = self::monthAndDayToDayNumber($date->getMonth(), $date->getDay());
        }

        Check::range($dateOrDayNumber, self::MIN_NUMBER, self::MAX_DENORMALIZED);
        $this->number = $dateOrDayNumber;

        if ($dateOrDayNumber > self::MAX_NUMBER) {
            $dateOrDayNumber -= self::MAX_NUMBER;
        }
        foreach (Month::getLengths(true) as $month => $days) {
            if ($dateOrDayNumber <= $days) {
                $this->month = $month;
                $this->dayOfMonth = $dateOrDayNumber;
                break;
            }
            $dateOrDayNumber -= $days;
        }
    }

    public static function createFromMonthAndDay(int $month, int $day): self
    {
        return new static(self::monthAndDayToDayNumber($month, $day));
    }

    private static function monthAndDayToDayNumber(int $month, int $day): int
    {
        Check::range($month, 1, 12);
        Check::range($day, 1, 31);

        $lengths = Month::getLengths(true);
        if ($day > $lengths[$month]) {
            throw new InvalidDateTimeException($month . '-' . $day);
        }

        $number = 0;
        foreach ($lengths as $m => $days) {
            if ($m === $month) {
                break;
            }
            $number += $days;
        }
        $number += $day;

        return $number;
    }

    public static function createFromDate(Date $date): self
    {
        return self::createFromMonthAndDay($date->getMonth(), $date->getDay());
    }

    public static function createFromDateTime(DateTime $dateTime): self
    {
        return self::createFromMonthAndDay($dateTime->getMonth(), $dateTime->getDay());
    }

    public function normalize(): self
    {
        if ($this->number <= self::MAX_NUMBER) {
            return $this;
        } else {
            return new static($this->number - self::MAX_NUMBER);
        }
    }

    public function denormalize(): self
    {
        if ($this->number >= self::MAX_NUMBER) {
            return $this;
        } else {
            return new static($this->number + self::MAX_NUMBER);
        }
    }

    public function isNormalized(): bool
    {
        return $this->number <= self::MAX_NUMBER;
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%d-%d %d #%s)',
            Cls::short(static::class),
            $this->month,
            $this->dayOfMonth,
            $this->number,
            Obj::dumpHash($this)
        );
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function modify(string $modify): self
    {
        $date = Date::createFromComponents(self::DEFAULT_FORMAT_YEAR, $this->month, $this->dayOfMonth);
        $that = self::createFromDate($date->modify($modify));

        if ($this->number > self::MAX_NUMBER) {
            $that = $that->denormalize();
        }

        return $that;
    }

    public function addDay(): self
    {
        $number = $this->number + 1;
        if ($number > self::MAX_DENORMALIZED) {
            $number -= self::MAX_NUMBER;
        }

        return new static($number);
    }

    public function addDays(int $days): self
    {
        $number = $this->number + $days;
        if ($number > self::MAX_DENORMALIZED) {
            $number -= self::MAX_NUMBER;
        }

        return new static($number);
    }

    public function subtractDay(): self
    {
        $number = $this->number - 1;
        if ($number < self::MIN_NUMBER) {
            $number += self::MAX_NUMBER;
        }

        return new static($number);
    }

    public function subtractDays(int $days): self
    {
        $number = $this->number - $days;
        if ($number < self::MIN_NUMBER) {
            $number += self::MAX_NUMBER;
        }

        return new static($number);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->toDate(self::DEFAULT_FORMAT_YEAR)->format($format);
    }

    public function toDate(int $year): Date
    {
        if ($this->number > self::MAX_NUMBER) {
            $year++;
        }

        return Date::createFromComponents($year, $this->month, $this->dayOfMonth);
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return ($this->number % self::MAX_NUMBER) === ($other->number % self::MAX_NUMBER);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return ($this->number % self::MAX_NUMBER) <=> ($other->number % self::MAX_NUMBER);
    }

    public function isBefore(self $other): bool
    {
        return ($this->number % self::MAX_NUMBER) < ($other->number % self::MAX_NUMBER);
    }

    public function isAfter(self $other): bool
    {
        return ($this->number % self::MAX_NUMBER) > ($other->number % self::MAX_NUMBER);
    }

    public function isSameOrBefore(self $other): bool
    {
        return ($this->number % self::MAX_NUMBER) <= ($other->number % self::MAX_NUMBER);
    }

    public function isSameOrAfter(self $other): bool
    {
        return ($this->number % self::MAX_NUMBER) >= ($other->number % self::MAX_NUMBER);
    }

    public function isBetween(self $since, self $until): bool
    {
        $sinceNumber = $since->number;
        $untilNumber = $until->number;
        $thisNumber = $this->number;

        if ($sinceNumber < $untilNumber) {
            return $thisNumber >= $sinceNumber && $thisNumber <= $untilNumber;
        } elseif ($sinceNumber > $untilNumber) {
            return $thisNumber >= $sinceNumber || $thisNumber <= $untilNumber;
        } else {
            return $thisNumber === $sinceNumber;
        }
    }

    // getters ---------------------------------------------------------------------------------------------------------

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getMonthEnum(): Month
    {
        return Month::get($this->month);
    }

    public function getDayOfMonth(): int
    {
        return $this->dayOfMonth;
    }

    // static ----------------------------------------------------------------------------------------------------------

    public static function min(self ...$items): self
    {
        return Arr::minBy($items, static function (self $time): int {
            return $time->number;
        });
    }

    public static function max(self ...$items): self
    {
        return Arr::maxBy($items, static function (self $time): int {
            return $time->number;
        });
    }

    /**
     * @param DayOfYear[] $items
     * @return DayOfYear[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $items, int $flags = Order::ASCENDING): array
    {
        return Arr::sortComparable($items, $flags);
    }

}
