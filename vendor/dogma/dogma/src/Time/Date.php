<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use DateInterval;
use DateTime as PhpDateTime;
use DateTimeInterface;
use DateTimeZone;
use Dogma\Arr;
use Dogma\Check;
use Dogma\Cls;
use Dogma\Comparable;
use Dogma\Dumpable;
use Dogma\Equalable;
use Dogma\Obj;
use Dogma\Order;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Format\DateTimeValues;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Time\Span\DateSpan;
use Throwable;
use function array_keys;
use function array_values;
use function explode;
use function gregoriantojd;
use function intval;
use function is_int;
use function is_string;
use function jdtogregorian;
use function sprintf;
use function str_replace;
use function strtolower;

/**
 * Date class.
 */
class Date implements DateOrDateTime, Pokeable, Dumpable
{
    use StrictBehaviorMixin;

    public const MIN = '0001-01-01';
    public const MAX = '9999-12-31';

    public const MIN_DAY_NUMBER = 1721426;
    public const MAX_DAY_NUMBER = 5373484;

    public const DEFAULT_FORMAT = 'Y-m-d';

    /** @var int */
    private $julianDay;

    /** @var DateTime|null */
    private $dateTime;

    /**
     * @param int|string $julianDayOrDateString
     */
    final public function __construct($julianDayOrDateString = 'today')
    {
        if (is_int($julianDayOrDateString)) {
            Check::range($julianDayOrDateString, self::MIN_DAY_NUMBER, self::MAX_DAY_NUMBER);
            $this->julianDay = $julianDayOrDateString;

            return;
        }

        $replacements = [
            'the day after tomorrow' => 'tomorrow +1 day',
            'overmorrow' => 'tomorrow +1 day',
            'the day before yesterday' => 'yesterday -1 day',
            'ereyesterday' => 'yesterday -1 day',
        ];
        $dateString = str_replace(array_keys($replacements), array_values($replacements), strtolower($julianDayOrDateString));

        try {
            $this->dateTime = (new DateTime($dateString))->setTime(0, 0, 0);
            $this->julianDay = self::calculateDayNumber($this->dateTime);
        } catch (Throwable $e) {
            throw new InvalidDateTimeException($dateString, $e);
        }
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): Date
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->getDate();
        } else {
            return DateTime::createFromDateTimeInterface($dateTime)->getDate();
        }
    }

    public static function createFromComponents(int $year, int $month, int $day): self
    {
        Check::range($year, 1, 9999);
        Check::range($month, 1, 12);
        Check::range($day, 1, 31);

        return new static("$year-$month-$day 00:00:00");
    }

    public static function createFromIsoYearAndWeek(int $year, int $week, int $dayOfWeek): self
    {
        Check::range($year, 1, 9999);
        Check::range($week, 1, 53);
        Check::range($dayOfWeek, 1, 7);

        $dateTime = new PhpDateTime('today 00:00:00');
        $dateTime->setISODate($year, $week, $dayOfWeek);

        return static::createFromDateTimeInterface($dateTime);
    }

    public static function createFromJulianDay(int $julianDay): self
    {
        return new static($julianDay);
    }

    public static function createFromFormat(string $format, string $timeString): self
    {
        $dateTime = PhpDateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new InvalidDateTimeException('xxx');
        }

        return self::createFromDateTimeInterface($dateTime);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        $this->getDateTime();
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%s %s #%s)',
            Cls::short(static::class),
            $this->format(),
            $this->julianDay,
            Obj::dumpHash($this)
        );
    }

    final public function __clone()
    {
        $this->dateTime = null;
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function modify(string $value): self
    {
        return static::createFromDateTimeInterface($this->getDateTime()->modify($value));
    }

    public function addDay(): self
    {
        return new static($this->julianDay + 1);
    }

    public function addDays(int $days): self
    {
        return new static($this->julianDay + $days);
    }

    public function subtractDay(): self
    {
        return new static($this->julianDay - 1);
    }

    public function subtractDays(int $days): self
    {
        return new static($this->julianDay - $days);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->getDateTime()->format($format);
    }

    public function toDateTime(?DateTimeZone $timeZone = null): DateTime
    {
        return DateTime::createFromDateAndTime($this, new Time(0), $timeZone);
    }

    public function toDateTimeInterval(?DateTimeZone $timeZone = null): DateTimeInterval
    {
        return new DateTimeInterval($this->getStart($timeZone), $this->addDay()->getStart());
    }

    public function getJulianDay(): int
    {
        return $this->julianDay;
    }

    /**
     * Returns Julian day number (count of days since January 1st, 4713 B.C.)
     * @return int
     */
    public static function calculateDayNumber(DateTimeInterface $dateTime): int
    {
        [$y, $m, $d] = explode('-', $dateTime->format(self::DEFAULT_FORMAT));

        return gregoriantojd(intval($m), intval($d), intval($y));
    }

    /**
     * @param DateTimeInterface|Date|string $other
     * @return DateInterval
     */
    public function diff($other, bool $absolute = false): DateInterval
    {
        if (is_string($other)) {
            $other = new DateTime($other);
        } elseif ($other instanceof self) {
            $other = new DateTime($other->format());
        }

        return (new DateTime($this->format()))->diff($other, $absolute);
    }

    /**
     * @param DateTimeInterface|Date|string $other
     * @return DateSpan
     */
    public function difference($other, bool $absolute = false): DateSpan
    {
        $interval = $this->diff($other, $absolute);

        return DateSpan::createFromDateInterval($interval);
    }

    public function getStart(?DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(0, 0, 0);
    }

    public function getEnd(?DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(23, 59, 59, 999999);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->julianDay <=> $other->julianDay;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->julianDay === $other->julianDay;
    }

    /**
     * @param Date|string $date
     * @return bool
     */
    public function isBefore($date): bool
    {
        if (is_string($date)) {
            $date = new static($date);
        }

        return $this->julianDay < $date->julianDay;
    }

    /**
     * @param Date|string $date
     * @return bool
     */
    public function isAfter($date): bool
    {
        if (is_string($date)) {
            $date = new static($date);
        }

        return $this->julianDay > $date->julianDay;
    }

    /**
     * @param Date|string $date
     * @return bool
     */
    public function isSameOrBefore($date): bool
    {
        if (is_string($date)) {
            $date = new static($date);
        }

        return $this->julianDay <= $date->julianDay;
    }

    /**
     * @param Date|string $date
     * @return bool
     */
    public function isSameOrAfter($date): bool
    {
        if (is_string($date)) {
            $date = new static($date);
        }

        return $this->julianDay >= $date->julianDay;
    }

    /**
     * @param Date|string $sinceDate
     * @param Date|string $untilDate
     * @return bool
     */
    public function isBetween($sinceDate, $untilDate): bool
    {
        if (is_string($sinceDate)) {
            $sinceDate = new static($sinceDate);
        }
        if (is_string($untilDate)) {
            $untilDate = new static($untilDate);
        }

        return $this->julianDay >= $sinceDate->julianDay && $this->julianDay <= $untilDate->julianDay;
    }

    public function isFuture(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date();

        return $this->julianDay > $today->julianDay;
    }

    public function isPast(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date();

        return $this->julianDay < $today->julianDay;
    }

    /**
     * @param int|string|DayOfWeek $day
     * @return bool
     */
    public function isDayOfWeek($day): bool
    {
        if (is_int($day)) {
            $day = DayOfWeek::get($day);
        } elseif (is_string($day)) {
            $day = DayOfWeek::getByName($day);
        }

        return (($this->julianDay % 7) + 1) === $day->getValue();
    }

    public function isWeekend(): bool
    {
        return (($this->julianDay % 7) + 1) > DayOfWeek::FRIDAY;
    }

    /**
     * @param int|string|Month $month
     * @return bool
     */
    public function isMonth($month): bool
    {
        if (is_int($month)) {
            $month = Month::get($month);
        } elseif (is_string($month)) {
            $month = Month::getByName($month);
        }

        return (int) $this->format('n') === $month->getValue();
    }

    // getters ---------------------------------------------------------------------------------------------------------

    private function getDateTime(): DateTime
    {
        if ($this->dateTime === null) {
            [$m, $d, $y] = explode('/', jdtogregorian($this->julianDay));

            $this->dateTime = new DateTime($y . '-' . $m . '-' . $d . ' 00:00:00');
        }

        return $this->dateTime;
    }

    public function getYear(): int
    {
        return (int) $this->format('Y');
    }

    public function getMonth(): int
    {
        return (int) $this->format('m');
    }

    public function getMonthEnum(): Month
    {
        return Month::get((int) $this->format('n'));
    }

    public function getDay(): int
    {
        return (int) $this->format('d');
    }

    public function getDayOfWeek(): int
    {
        return ($this->julianDay % 7) + 1;
    }

    public function getDayOfWeekEnum(): DayOfWeek
    {
        return DayOfWeek::get(($this->julianDay % 7) + 1);
    }

    public function fillValues(DateTimeValues $values): void
    {
        $results = explode('|', $this->format('Y|L|z|m|d|N|W|o'));

        $values->year = (int) $results[0];
        $values->leapYear = (bool) $results[1];
        $values->dayOfYear = (int) $results[2];
        $values->month = (int) $results[3];
        $values->quarter = (int) ($values->month / 3);
        $values->day = (int) $results[4];
        $values->dayOfWeek = (int) $results[5];
        $values->weekOfYear = (int) $results[6];
        $values->isoWeekYear = (int) $results[7];
    }

    // static ----------------------------------------------------------------------------------------------------------

    public static function min(self ...$items): self
    {
        return Arr::minBy($items, static function (self $date): int {
            return $date->julianDay;
        });
    }

    public static function max(self ...$items): self
    {
        return Arr::maxBy($items, static function (self $date): int {
            return $date->julianDay;
        });
    }

    /**
     * @param Date[] $items
     * @return Date[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $items, int $flags = Order::ASCENDING): array
    {
        return Arr::sortComparable($items, $flags);
    }

}
