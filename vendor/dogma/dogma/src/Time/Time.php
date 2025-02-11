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
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Format\DateTimeValues;
use Dogma\Time\Span\TimeSpan;
use Throwable;
use function explode;
use function floor;
use function is_int;
use function is_string;
use function ltrim;
use function preg_match;
use function round;
use function sprintf;

/**
 * Time of day without a date and timezone.
 *
 * Times like 27:00:00 (up to 48 hours) can be created.
 * TimeInterval will automatically (de)normalize end of interval after midnight to value higher than 24:00:00.
 * When compared 27:00:00 will be equal to 03:00:00 (modulo arithmetic).
 * When formatted 27:00:00 will result in "03:00:00".
 */
class Time implements DateTimeOrTime, Pokeable, Dumpable
{
    use StrictBehaviorMixin;

    public const MIN = '00:00:00.000000';
    public const MAX = '23:59:59.999999';

    public const MIN_MICROSECONDS = 0;
    public const MAX_MICROSECONDS = Microseconds::DAY - 1;
    private const MAX_DENORMALIZED = self::MAX_MICROSECONDS + Microseconds::DAY;

    public const DEFAULT_FORMAT = 'H:i:s.u';

    /** @var int */
    private $microseconds;

    /** @var DateTime|null */
    private $dateTime;

    /**
     * @param int|string $microsecondsOrTimeString
     */
    final public function __construct($microsecondsOrTimeString)
    {
        if (is_int($microsecondsOrTimeString)) {
            Check::range($microsecondsOrTimeString, self::MIN_MICROSECONDS, self::MAX_DENORMALIZED);

            $this->microseconds = $microsecondsOrTimeString;
        } elseif (preg_match('/^([0-4]?[0-9])[:.]([0-5]?[0-9])(?:[:.]([0-5]?[0-9](\\.[0-9]{1,6})?))?$/', $microsecondsOrTimeString, $m)) {
            $hours = (int) $m[1];
            $minutes = (int) $m[2];
            $seconds = isset($m[3]) ? (int) $m[3] : 0;
            $microseconds = isset($m[4]) ? (int) Str::padRight(ltrim($m[4], '.'), 6, '0') : 0;

            $total = ($hours * 3600 + $minutes * 60 + $seconds) * 1000000 + $microseconds;
            if ($total > self::MAX_DENORMALIZED) {
                throw new InvalidDateTimeException($microsecondsOrTimeString);
            }
            $this->microseconds = $total;
        } else {
            try {
                $dateTime = new PhpDateTime($microsecondsOrTimeString);
            } catch (Throwable $e) {
                throw new InvalidDateTimeException($microsecondsOrTimeString, $e);
            }

            $hours = (int) $dateTime->format('H');
            $minutes = (int) $dateTime->format('i');
            $seconds = (int) $dateTime->format('s');
            $microseconds = (int) $dateTime->format('u');

            $this->microseconds = ($hours * 3600 + $minutes * 60 + $seconds) * 1000000 + $microseconds;
        }
    }

    public static function createFromSeconds(int $secondsSinceMidnight): self
    {
        return new static($secondsSinceMidnight * 1000000);
    }

    public static function createFromComponents(int $hours, int $minutes = 0, int $seconds = 0, int $microseconds = 0): self
    {
        Check::range($hours, 0, 47);
        Check::range($minutes, 0, 59);
        Check::range($seconds, 0, 59);
        Check::range($microseconds, 0, 999999);

        return new static(($hours * 3600 + $minutes * 60 + $seconds) * 1000000 + $microseconds);
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): Time
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->getTime();
        } else {
            return DateTime::createFromDateTimeInterface($dateTime)->getTime();
        }
    }

    public static function createFromFormat(string $format, string $timeString): self
    {
        $dateTime = PhpDateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new InvalidDateTimeException('xxx');
        }

        $hours = (int) $dateTime->format('h');
        $minutes = (int) $dateTime->format('i');
        $seconds = (int) $dateTime->format('s');
        $microseconds = (int) $dateTime->format('u');

        return self::createFromComponents($hours, $minutes, $seconds, $microseconds);
    }

    public static function validateComponents(int $hours, int $minutes = 0, int $seconds = 0, int $microseconds = 0): bool
    {
        return $hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes >= 59 && $seconds >= 0 && $seconds >= 59 && $microseconds >= 0 && $microseconds <= 999999;
    }

    public function normalize(): self
    {
        if ($this->microseconds <= self::MAX_MICROSECONDS) {
            return $this;
        } else {
            return new static($this->microseconds % Microseconds::DAY);
        }
    }

    public function denormalize(): self
    {
        if ($this->microseconds >= self::MAX_MICROSECONDS) {
            return $this;
        } else {
            return new static($this->microseconds + Microseconds::DAY);
        }
    }

    public function isNormalized(): bool
    {
        return $this->microseconds <= self::MAX_MICROSECONDS;
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
            $this->microseconds,
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
        $denormalized = $this->microseconds >= self::MAX_MICROSECONDS;
        $that = static::createFromDateTimeInterface($this->getDateTime()->modify($value));

        if ($denormalized) {
            return $that->denormalize();
        }

        return $that;
    }

    /**
     * Round to the closest value from given list of values for given unit
     * (e.g. 15:36:15 * minutes[0, 10, 20, 30, 40 50] --> 15:40:00)
     * @param int[]|null $allowedValues
     * @return Time
     */
    public function roundTo(DateTimeUnit $unit, ?array $allowedValues = null): self
    {
        /** @var self $that */
        $that = TimeCalc::roundTo($this, $unit, $allowedValues);

        return $that;
    }

    /**
     * Round to firs upper value from given list of values for given unit
     * (e.g. 15:32:15 * minutes[0, 10, 20, 30, 40 50] --> 15:40:00)
     * @param int[]|null $allowedValues
     * @return Time
     */
    public function roundUpTo(DateTimeUnit $unit, ?array $allowedValues = null): self
    {
        /** @var self $that */
        $that = TimeCalc::roundUpTo($this, $unit, $allowedValues);

        return $that;
    }

    /**
     * Round to firs lower value from given list of values for given unit
     * (e.g. 15:36:15 * minutes[0, 10, 20, 30, 40 50] --> 15:30:00)
     * @param int[]|null $allowedValues
     * @return Time
     */
    public function roundDownTo(DateTimeUnit $unit, ?array $allowedValues = null): self
    {
        /** @var self $that */
        $that = TimeCalc::roundDownTo($this, $unit, $allowedValues);

        return $that;
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->getDateTime()->format($format);
    }

    public function toDateTime(?Date $date = null, ?DateTimeZone $timeZone = null): DateTime
    {
        return DateTime::createFromDateAndTime($date ?? new Date(), $this, $timeZone);
    }

    /**
     * @param DateTimeInterface|Time|string $other
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
     * @param DateTimeInterface|Time|string $other
     * @return TimeSpan
     */
    public function difference($other, bool $absolute = false): TimeSpan
    {
        $interval = $this->diff($other, $absolute);

        return TimeSpan::createFromDateInterval($interval);
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return ($this->microseconds % Microseconds::DAY) === ($other->microseconds % Microseconds::DAY);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return ($this->microseconds % Microseconds::DAY) <=> ($other->microseconds % Microseconds::DAY);
    }

    /**
     * @param Time|string $time
     * @return bool
     */
    public function isBefore($time): bool
    {
        if (is_string($time)) {
            $time = new static($time);
        }

        return ($this->microseconds % Microseconds::DAY) < ($time->microseconds % Microseconds::DAY);
    }

    /**
     * @param Time|string $time
     * @return bool
     */
    public function isAfter($time): bool
    {
        if (is_string($time)) {
            $time = new static($time);
        }

        return ($this->microseconds % Microseconds::DAY) > ($time->microseconds % Microseconds::DAY);
    }

    /**
     * @param Time|string $time
     * @return bool
     */
    public function isSameOrBefore($time): bool
    {
        if (is_string($time)) {
            $time = new static($time);
        }

        return ($this->microseconds % Microseconds::DAY) <= ($time->microseconds % Microseconds::DAY);
    }

    /**
     * @param Time|string $time
     * @return bool
     */
    public function isSameOrAfter($time): bool
    {
        if (is_string($time)) {
            $time = new static($time);
        }

        return ($this->microseconds % Microseconds::DAY) >= ($time->microseconds % Microseconds::DAY);
    }

    /**
     * @param Time|string $since
     * @param Time|string $until
     * @return bool
     */
    public function isBetween($since, $until): bool
    {
        if (is_string($since)) {
            $since = new static($since);
        }
        if (is_string($until)) {
            $until = new static($until);
        }

        $sinceTime = $since->microseconds % Microseconds::DAY;
        $untilTime = $until->microseconds % Microseconds::DAY;
        $thisTime = $this->microseconds % Microseconds::DAY;

        if ($sinceTime < $untilTime) {
            return $thisTime >= $sinceTime && $thisTime <= $untilTime;
        } elseif ($sinceTime > $untilTime) {
            return $thisTime >= $sinceTime || $thisTime <= $untilTime;
        } else {
            return $thisTime === $sinceTime;
        }
    }

    public function isMidnight(): bool
    {
        return $this->microseconds === 0 || $this->microseconds === Microseconds::DAY;
    }

    // getters ---------------------------------------------------------------------------------------------------------

    private function getDateTime(): DateTime
    {
        if ($this->dateTime === null) {
            $total = $this->microseconds % Microseconds::DAY;
            $seconds = (int) floor($total / 1000000);
            $microseconds = $total - ($seconds * 1000000);
            $this->dateTime = new DateTime(DateTime::MIN . ' +' . $seconds . ' seconds +' . $microseconds . ' microseconds');
        }

        return $this->dateTime;
    }

    public function getMicroTime(): int
    {
        return $this->microseconds;
    }

    public function getHours(): int
    {
        return (int) floor(($this->microseconds % Microseconds::DAY) / 1000000 / 3600);
    }

    public function getMinutes(): int
    {
        return floor($this->microseconds / 1000000 / 60) % 60;
    }

    public function getSeconds(): int
    {
        return floor($this->microseconds / 1000000) % 60;
    }

    public function getMiliseconds(): int
    {
        return (int) round(($this->microseconds % 1000000) / 1000);
    }

    public function getMicroseconds(): int
    {
        return $this->microseconds % 1000000;
    }

    public function hasSeconds(): bool
    {
        return ($this->microseconds % 60000000) !== 0;
    }

    public function hasMicroseconds(): bool
    {
        return ($this->microseconds % 1000000) !== 0;
    }

    public function fillValues(DateTimeValues $values): void
    {
        $results = explode('|', $this->format('H|i|s|v|u'));

        $values->hours = (int) $results[0];
        $values->minutes = (int) $results[1];
        $values->seconds = (int) $results[2];
        $values->miliseconds = (int) $results[3];
        $values->microseconds = (int) $results[4];
    }

    // static ----------------------------------------------------------------------------------------------------------

    public static function min(self ...$items): self
    {
        return Arr::minBy($items, static function (self $time): int {
            return $time->microseconds;
        });
    }

    public static function max(self ...$items): self
    {
        return Arr::maxBy($items, static function (self $time): int {
            return $time->microseconds;
        });
    }

    /**
     * @param Time[] $items
     * @return Time[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $items, int $flags = Order::ASCENDING): array
    {
        return Arr::sortComparable($items, $flags);
    }

}
