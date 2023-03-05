<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Span;

use DateInterval;
use Dogma\Arr;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\InvalidValueException;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use function abs;
use function floor;
use function round;

/**
 * Replacement of native DateInterval
 * - immutable!
 * - capable of holding mixed sign offsets (eg: "+1 year, -2 months")
 * - calculations and rounding
 * - microseconds
 *
 * Since interval is not anchored to some starting time, exact size of some time units is not known.
 * Therefore in calculations where different units are compared or calculated:
 * - every month has 30 days
 * - every year has 365 days
 * - every day has 24 hours
 * This means that calculations containing normalization and denormalization (eg: getXyzTotal()) are not commutative
 * and may lead to unexpected results. For precise time calculations use timestamps instead.
 */
class DateTimeSpan implements DateOrTimeSpan
{
    use StrictBehaviorMixin;

    public const DEFAULT_FORMAT = 'y-m-d h:i:S';

    /** @var  int */
    private $years;

    /** @var int */
    private $months;

    /** @var int */
    private $days;

    /** @var int */
    private $hours;

    /** @var int */
    private $minutes;

    /** @var int */
    private $seconds;

    /** @var int */
    private $microseconds;

    final public function __construct(
        int $years,
        int $months = 0,
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0,
        int $microseconds = 0
    ) {
        $this->years = $years;
        $this->months = $months;
        $this->days = $days;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->microseconds = $microseconds;
    }

    public static function createFromDateInterval(DateInterval $dateInterval): self
    {
        $self = new static(0);
        $self->years = $dateInterval->invert ? -$dateInterval->y : $dateInterval->y;
        $self->months = $dateInterval->invert ? -$dateInterval->m : $dateInterval->m;
        $self->days = $dateInterval->invert ? -$dateInterval->d : $dateInterval->d;
        $self->hours = $dateInterval->invert ? -$dateInterval->h : $dateInterval->h;
        $self->minutes = $dateInterval->invert ? -$dateInterval->i : $dateInterval->i;
        $self->seconds = $dateInterval->invert ? -$dateInterval->s : $dateInterval->s;
        $self->microseconds = $dateInterval->invert ? (int) (-$dateInterval->f * 1000000) : (int) ($dateInterval->f * 1000);

        return $self;
    }

    public static function createFromDateIntervalString(string $string): self
    {
        $dateInterval = new DateInterval($string);

        return self::createFromDateInterval($dateInterval);
    }

    public static function createFromDateString(string $string): self
    {
        // todo: works properly since 7.3.3 etc. before that returned empty interval on invalid input. should check this
        /** @var DateInterval|false $dateInterval */
        $dateInterval = DateInterval::createFromDateString($string);
        if ($dateInterval === false) {
            throw new InvalidValueException($string, 'date-time span string');
        }

        return self::createFromDateInterval($dateInterval);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    /**
     * Subtracts positive and negative values if needed
     * @return DateInterval
     */
    public function toNative(): DateInterval
    {
        if ($this->isPositive() || $this->isNegative()) {
            return self::toNativeSimple($this);
        }
        $that = $this;
        $inverted = false;
        if ($this->getYearsFraction() < 0.0) {
            $that = $this->invert();
            $inverted = true;
        }

        $years = $that->years;
        $months = $that->months;
        $days = $that->days;
        $hours = $that->hours;
        $minutes = $that->minutes;
        $seconds = $that->seconds;
        $microseconds = $that->microseconds;

        if ($microseconds < 0) {
            $moveSeconds = (int) ($microseconds / 1000000) - 1;
            $seconds += $moveSeconds;
            $microseconds += $moveSeconds * 1000000;
        }
        if ($seconds < 0) {
            $moveMinutes = (int) ($seconds / 60) - 1;
            $minutes += $moveMinutes;
            $seconds += $moveMinutes * 60;
        }
        if ($minutes < 0) {
            $moveHours = (int) ($minutes / 60) - 1;
            $hours += $moveHours;
            $minutes += $moveHours * 60;
        }
        if ($hours < 0) {
            $moveDays = (int) ($hours / 24) - 1;
            $days += $moveDays;
            $hours += $moveDays * 24;
        }
        if ($days < 0) {
            $moveMonths = (int) ($days / 30) - 1;
            $months += $moveMonths;
            $days += $moveMonths * 30;
        }
        if ($months < 0) {
            $moveYears = (int) ($years / 12) - 1;
            $years += $moveYears;
            $months += $moveYears * 12;
        }
        if ($years < 0) {
            throw new ShouldNotHappenException('Years should always be positive at this point.');
        }

        $span = new self($years, $months, $days, $hours, $minutes, $seconds, $microseconds);
        if ($inverted) {
            $span = $span->invert();
        }

        return self::toNativeSimple($span);
    }

    private static function toNativeSimple(self $that): DateInterval
    {
        $dateInterval = new DateInterval('P0Y');

        if ($that->isNegative()) {
            $dateInterval->invert = 1;
            $that = $that->invert();
        }
        $dateInterval->y = $that->years;
        $dateInterval->m = $that->months;
        $dateInterval->d = $that->days;
        $dateInterval->h = $that->hours;
        $dateInterval->i = $that->minutes;
        $dateInterval->s = $that->seconds;
        $dateInterval->f = $that->microseconds / 1000000;

        return $dateInterval;
    }

    /**
     * Separates positive and negative values to two instances
     * @return DateInterval[] ($positive, $negative)
     */
    public function toPositiveAndNegative(): array
    {
        $positive = new DateInterval('P0Y');
        $negative = new DateInterval('P0Y');
        $negative->invert = 1;

        if ($this->years >= 0) {
            $positive->y = $this->years;
        } else {
            $negative->y = -$this->years;
        }
        if ($this->months >= 0) {
            $positive->m = $this->months;
        } else {
            $negative->m = -$this->months;
        }
        if ($this->days >= 0) {
            $positive->d = $this->days;
        } else {
            $negative->d = -$this->days;
        }
        if ($this->hours >= 0) {
            $positive->h = $this->hours;
        } else {
            $negative->h = -$this->hours;
        }
        if ($this->minutes >= 0) {
            $positive->i = $this->minutes;
        } else {
            $negative->i = -$this->minutes;
        }
        if ($this->seconds >= 0) {
            $positive->s = $this->seconds;
        } else {
            $negative->s = -$this->seconds;
        }

        if ($this->microseconds >= 0) {
            $positive->f = $this->microseconds / 1000000;
        } else {
            $negative->f = -$this->microseconds / 1000000;
        }

        return [$positive, $negative];
    }

    public function getDatePart(): DateSpan
    {
        return new DateSpan($this->years, $this->months, $this->days);
    }

    public function getTimePart(): TimeSpan
    {
        return new TimeSpan($this->hours, $this->minutes, $this->seconds, $this->microseconds);
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeSpanFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new DateTimeSpanFormatter();
        }

        return $formatter->format($this, $format);
    }

    /**
     * @param DateTimeSpan $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        return $this->getValues() === $other->getValues();
    }

    /**
     * @param DateTimeSpan $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        return $this->years <=> $other->years
            ?: $this->months <=> $other->months
            ?: $this->days <=> $other->days
            ?: $this->hours <=> $other->hours
            ?: $this->minutes <=> $other->minutes
            ?: $this->seconds <=> $other->seconds
            ?: $this->microseconds <=> $other->microseconds;
    }

    public function isZero(): bool
    {
        return $this->years === 0
            && $this->months === 0
            && $this->days === 0
            && $this->hours === 0
            && $this->minutes === 0
            && $this->seconds === 0
            && $this->microseconds === 0;
    }

    public function isMixed(): bool
    {
        return !$this->isPositive() && !$this->isNegative();
    }

    private function isPositive(): bool
    {
        return $this->years >= 0
            && $this->months >= 0
            && $this->days >= 0
            && $this->hours >= 0
            && $this->minutes >= 0
            && $this->seconds >= 0
            && $this->microseconds >= 0;
    }

    private function isNegative(): bool
    {
        return $this->years < 0
            && $this->months < 0
            && $this->days < 0
            && $this->hours < 0
            && $this->minutes < 0
            && $this->seconds < 0
            && $this->microseconds < 0;
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function add(self ...$other): self
    {
        $that = clone($this);
        foreach ($other as $span) {
            $that->years += $span->years;
            $that->months += $span->months;
            $that->days += $span->days;
            $that->hours += $span->hours;
            $that->minutes += $span->minutes;
            $that->seconds += $span->seconds;
            $that->microseconds += $span->microseconds;
        }

        return $that->normalize(true);
    }

    public function subtract(self ...$other): self
    {
        return $this->add(...Arr::map($other, static function (DateTimeSpan $span): DateTimeSpan {
            return $span->invert();
        }));
    }

    public function invert(): self
    {
        return new self(-$this->years, -$this->months, -$this->days, -$this->hours, -$this->minutes, -$this->seconds, -$this->microseconds);
    }

    public function abs(): self
    {
        if ($this->getYearsFraction() >= 0.0) {
            return $this;
        } else {
            return $this->invert();
        }
    }

    /**
     * Normalizes values by summarizing smaller units into bigger. eg: '34 days' -> '1 month, 4 days'
     * @return self
     */
    public function normalize(bool $safeOnly = false): self
    {
        $microseconds = $this->microseconds;
        $seconds = $this->seconds;
        $minutes = $this->minutes;
        $hours = $this->hours;
        $days = $this->days;
        $months = $this->months;
        $years = $this->years;

        if ($microseconds >= 1000000) {
            $seconds += (int) ($microseconds / 1000000);
            $microseconds %= 1000000;
        } elseif ($microseconds <= -1000000) {
            $seconds += (int) ($microseconds / 1000000);
            $microseconds %= 1000000;
        }
        if ($seconds >= 60) {
            $minutes += (int) ($seconds / 60);
            $seconds %= 60;
        } elseif ($seconds <= -60) {
            $minutes += (int) ($seconds / 60);
            $seconds %= 60;
        }
        if ($minutes >= 60) {
            $hours += (int) ($minutes / 60);
            $minutes %= 60;
        } elseif ($minutes <= -60) {
            $hours += (int) ($minutes / 60);
            $minutes %= 60;
        }

        if ($safeOnly) {
            return new self($years, $months, $days, $hours, $minutes, $seconds, $microseconds);
        }

        if ($hours >= 24) {
            $days += (int) ($hours / 24);
            $hours %= 24;
        } elseif ($hours <= -24) {
            $days += (int) ($hours / 24);
            $hours %= 24;
        }
        if ($days >= 30) {
            $months += (int) ($days / 30);
            $days %= 30;
        } elseif ($days <= -30) {
            $months += (int) ($days / 30);
            $days %= 30;
        }
        if ($months >= 12) {
            $years += (int) ($months / 12);
            $months %= 12;
        } elseif ($months <= -12) {
            $years += (int) ($months / 12);
            $months %= 12;
        }

        return new self($years, $months, $days, $hours, $minutes, $seconds, $microseconds);
    }

    public function roundToTwoValues(bool $useWeeks = false): self
    {
        $years = $this->getYearsFraction();
        if (abs($years) >= 1) {
            $wholeYears = (int) round($years);
            $months = (int) round(($years - $wholeYears) * 12);
            if (abs($months) === 12) {
                $wholeYears += (int) ($months / 12);
                $months = 0;
            }
            return new self($wholeYears, $months);
        }

        $months = $this->getMonthsFraction();
        if (abs($months) >= 1) {
            $wholeMonths = (int) round($months);
            $days = (int) round(($months - $wholeMonths) * 30);
            if (abs($days) === 30) {
                $wholeMonths += (int) ($days / 30);
                $days = 0;
            }
            if ($useWeeks) {
                $days = (int) (round($days / 7) * 7);
            }
            return new self(0, $wholeMonths, $days);
        }

        if ($useWeeks) {
            $weeks = $this->getWeeksFraction();
            if (abs($weeks) >= 1) {
                $days = (int) round($this->getDaysFraction());
                return new self(0, 0, $days);
            }
        }

        $days = $this->getDaysFraction();
        if (abs($days) >= 1) {
            $wholeDays = (int) round($days);
            $hours = (int) round(($days - $wholeDays) * 24);
            if (abs($hours) === 24) {
                $wholeDays += (int) ($hours / 24);
                $hours = 0;
            }
            return new self(0, 0, $wholeDays, $hours);
        }

        $hours = $this->getHoursTotal();
        if (abs($hours) >= 1) {
            $wholeHours = (int) round($hours);
            $minutes = (int) round(($hours - $wholeHours) * 60);
            if (abs($minutes) === 60) {
                $wholeHours += (int) ($minutes / 60);
                $minutes = 0;
            }
            return new self(0, 0, 0, $wholeHours, $minutes);
        }

        $minutes = $this->getMinutesFraction();
        if (abs($minutes) >= 1) {
            $wholeMinutes = (int) round($minutes);
            $seconds = (int) round(($minutes - $wholeMinutes) * 60);
            if (abs($seconds) === 60) {
                $wholeMinutes += (int) ($seconds / 60);
                $seconds = 0;
            }
            return new self(0, 0, 0, 0, $wholeMinutes, $seconds);
        }

        return new self(0, 0, 0, 0, 0, $this->seconds, $this->microseconds);
    }

    public function roundToSingleValue(bool $useWeeks = false): self
    {
        $years = (int) round($this->getYearsFraction());
        if (abs($years) >= 1) {
            return new self($years);
        }

        $months = (int) round($this->getMonthsFraction());
        if (abs($months) >= 1) {
            return new self(0, $months);
        }

        if ($useWeeks) {
            $weeks = (int) round($this->getWeeksFraction());
            if (abs($weeks) >= 1) {
                return new self(0, 0, $weeks * 7);
            }
        }

        $days = (int) round($this->getDaysFraction());
        if (abs($days) >= 1) {
            return new self(0, 0, $days);
        }

        $hours = (int) round($this->getHoursTotal());
        if (abs($hours) >= 1) {
            return new self(0, 0, 0, $hours);
        }

        $minutes = (int) round($this->getMinutesFraction());
        if (abs($minutes) >= 1) {
            return new self(0, 0, 0, 0, $minutes);
        }

        $seconds = (int) round($this->getSecondsFraction());
        if (abs($seconds) >= 1) {
            return new self(0, 0, 0, 0, 0, $seconds);
        }

        return new self(0, 0, 0, 0, 0, 0, $this->microseconds);
    }

    // getters ---------------------------------------------------------------------------------------------------------

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return [
            $this->years,
            $this->months,
            $this->days,
            $this->hours,
            $this->minutes,
            $this->seconds,
            $this->microseconds,
        ];
    }

    public function getYears(): int
    {
        return $this->years;
    }

    public function getYearsFraction(): float
    {
        return $this->years
            + $this->months / 12
            + $this->days / 365
            + $this->hours / 365 / 24
            + $this->minutes / 365 / 24 / 60
            + $this->seconds / 365 / 24 / 60 / 60
            + $this->microseconds / 365 / 24 / 60 / 60 / 1000000;
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function getMonthsTotal(): float
    {
        return $this->getMonthsFraction()
            + $this->years * 12;
    }

    public function getMonthsFraction(): float
    {
        return $this->months
            + $this->days / 30
            + $this->hours / 30 / 24
            + $this->minutes / 30 / 24 / 60
            + $this->seconds / 30 / 24 / 60 / 60
            + $this->microseconds / 30 / 24 / 60 / 60 / 1000000;
    }

    public function getWeeks(): int
    {
        return (int) floor($this->days / 7);
    }

    public function getWeeksTotal(): float
    {
        return $this->getDaysTotal() / 7;
    }

    public function getWeeksFraction(): float
    {
        return $this->getDaysFraction() / 7;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function getDaysTotal(): float
    {
        return $this->getDaysFraction()
            + $this->months * 30
            + $this->years * 12 * 30;
    }

    public function getDaysFraction(): float
    {
        return $this->days
            + $this->hours / 24
            + $this->minutes / 24 / 60
            + $this->seconds / 24 / 60 / 60
            + $this->microseconds / 24 / 60 / 60 / 1000000;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getHoursTotal(): float
    {
        return $this->getHoursFraction()
            + $this->days * 24
            + $this->months * 30 * 24
            + $this->years * 12 * 30 * 24;
    }

    public function getHoursFraction(): float
    {
        return $this->hours
            + $this->minutes / 60
            + $this->seconds / 60 / 60
            + $this->microseconds / 60 / 60 / 1000000;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getMinutesTotal(): float
    {
        return $this->getMinutesFraction()
            + $this->hours * 60
            + $this->days * 24 * 60
            + $this->months * 30 * 24 * 60
            + $this->years * 12 * 30 * 24 * 60;
    }

    public function getMinutesFraction(): float
    {
        return $this->minutes
            + $this->seconds / 60
            + $this->microseconds / 60 / 1000000;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function getSecondsTotal(): float
    {
        return $this->getSecondsFraction()
            + $this->minutes * 60
            + $this->hours * 60 * 60
            + $this->days * 24 * 60 * 60
            + $this->months * 30 * 24 * 60 * 60
            + $this->years * 12 * 30 * 24 * 60 * 60;
    }

    public function getSecondsFraction(): float
    {
        return $this->seconds
            + $this->microseconds / 1000000;
    }

    public function getMicroseconds(): int
    {
        return $this->microseconds;
    }

    public function getMicrosecondsTotal(): int
    {
        return $this->microseconds
            + $this->seconds * 1000000
            + $this->minutes * 60 * 1000000
            + $this->hours * 60 * 60 * 1000000
            + $this->days * 24 * 60 * 60 * 1000000
            + $this->months * 30 * 24 * 60 * 60 * 1000000
            + $this->years * 12 * 30 * 24 * 60 * 60 * 1000000;
    }

}
