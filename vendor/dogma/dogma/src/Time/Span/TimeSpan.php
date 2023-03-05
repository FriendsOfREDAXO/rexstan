<?php declare(strict_types = 1);

namespace Dogma\Time\Span;

use DateInterval;
use Dogma\Arr;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\InvalidValueException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\InvalidTimeSpanException;

/**
 * Time interval without fixed start and end time. Under 24 hours only.
 */
class TimeSpan implements DateOrTimeSpan
{
    use StrictBehaviorMixin;

    public const DEFAULT_FORMAT = 'h:i:S';

    /** @var  int */
    private $hours;

    /** @var int */
    private $minutes;

    /** @var int */
    private $seconds;

    /** @var int */
    private $microseconds;

    final public function __construct(
        int $hours,
        int $minutes = 0,
        int $seconds = 0,
        int $microseconds = 0
    ) {
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->microseconds = $microseconds;
    }

    public static function createFromDateInterval(DateInterval $dateInterval): self
    {
        $self = new static(0);
        $self->hours = $dateInterval->invert ? -$dateInterval->h : $dateInterval->h;
        $self->minutes = $dateInterval->invert ? -$dateInterval->i : $dateInterval->i;
        $self->seconds = $dateInterval->invert ? -$dateInterval->s : $dateInterval->s;
        $self->microseconds = (int) ($dateInterval->invert ? -$dateInterval->f : $dateInterval->f);

        if ($dateInterval->y !== 0 || $dateInterval->m !== 0 || $dateInterval->d !== 0) {
            throw new InvalidTimeSpanException($dateInterval->format('%r %y-%m-%d %h-%i-%s.%f'));
        }

        return $self;
    }

    public static function createFromDateIntervalString(string $string): self
    {
        $dateInterval = new DateInterval($string);

        return self::createFromDateInterval($dateInterval);
    }

    public static function createFromDateString(string $string): self
    {
        /** @var DateInterval|false $dateInterval */
        $dateInterval = DateInterval::createFromDateString($string);
        if ($dateInterval === false) {
            throw new InvalidValueException($string, 'time span string');
        }

        return self::createFromDateInterval($dateInterval);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function toDateTimeSpan(): DateTimeSpan
    {
        return new DateTimeSpan(0, 0, 0, $this->hours, $this->minutes, $this->seconds, $this->microseconds);
    }

    public function toNative(): DateInterval
    {
        return $this->toDateTimeSpan()->toNative();
    }

    /**
     * @return DateInterval[]
     */
    public function toPositiveAndNegative(): array
    {
        return $this->toDateTimeSpan()->toPositiveAndNegative();
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeSpanFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new DateTimeSpanFormatter();
        }

        return $formatter->format($this->toDateTimeSpan(), $format);
    }

    /**
     * @param TimeSpan $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        return $this->getValues() === $other->getValues();
    }

    /**
     * @param TimeSpan $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        return $this->hours <=> $other->hours
            ?: $this->minutes <=> $other->minutes
            ?: $this->seconds <=> $other->seconds
            ?: $this->microseconds <=> $other->microseconds;
    }

    public function isZero(): bool
    {
        return $this->hours === 0
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
        return $this->hours >= 0
            && $this->minutes >= 0
            && $this->seconds >= 0
            && $this->microseconds >= 0;
    }

    private function isNegative(): bool
    {
        return $this->hours < 0
            && $this->minutes < 0
            && $this->seconds < 0
            && $this->microseconds < 0;
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function add(self ...$other): self
    {
        $that = clone($this);
        foreach ($other as $span) {
            $that->hours += $span->hours;
            $that->minutes += $span->minutes;
            $that->seconds += $span->seconds;
            $that->microseconds += $span->microseconds;
        }

        return $that->normalize();
    }

    public function subtract(self ...$other): self
    {
        return $this->add(...Arr::map($other, static function (DateTimeSpan $span): DateTimeSpan {
            return $span->invert();
        }));
    }

    public function invert(): self
    {
        return new self(-$this->hours, -$this->minutes, -$this->seconds, -$this->microseconds);
    }

    public function abs(): self
    {
        if ($this->getHoursFraction() >= 0.0) {
            return $this;
        } else {
            return $this->invert();
        }
    }

    /**
     * Normalizes values by summarizing smaller units into bigger. eg: '34 days' -> '1 month, 4 days'
     * @return self
     */
    public function normalize(): self
    {
        $microseconds = $this->microseconds;
        $seconds = $this->seconds;
        $minutes = $this->minutes;
        $hours = $this->hours;

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

        return new self($hours, $minutes, $seconds, $microseconds);
    }

    // getters ---------------------------------------------------------------------------------------------------------

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return [
            $this->hours,
            $this->minutes,
            $this->seconds,
            $this->microseconds,
        ];
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getHoursTotal(): float
    {
        return $this->getHoursFraction();
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
            + $this->hours * 60;
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
            + $this->hours * 60 * 60;
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
            + $this->hours * 60 * 60 * 1000000;
    }

}
