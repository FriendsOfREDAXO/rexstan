<?php declare(strict_types = 1);

namespace Dogma\Time\Span;

use DateInterval;
use Dogma\Arr;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\InvalidValueException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\InvalidTimeSpanException;
use function floor;

/**
 * Time interval without fixed start time and end time. Whole days only.
 */
class DateSpan implements DateOrTimeSpan
{
    use StrictBehaviorMixin;

    public const DEFAULT_FORMAT = 'y-m-d';

    /** @var int */
    private $years;

    /** @var int */
    private $months;

    /** @var int */
    private $days;

    final public function __construct(
        int $years,
        int $months = 0,
        int $days = 0
    ) {
        $this->years = $years;
        $this->months = $months;
        $this->days = $days;
    }

    public static function createFromDateInterval(DateInterval $dateInterval): self
    {
        $self = new static(0);
        $self->years = $dateInterval->invert ? -$dateInterval->y : $dateInterval->y;
        $self->months = $dateInterval->invert ? -$dateInterval->m : $dateInterval->m;
        $self->days = $dateInterval->invert ? -$dateInterval->d : $dateInterval->d;

        if ($dateInterval->h !== 0 || $dateInterval->i !== 0 || $dateInterval->s !== 0 || $dateInterval->f !== 0.0) {
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
            throw new InvalidValueException($string, 'date span string');
        }

        return self::createFromDateInterval($dateInterval);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function toDateTimeSpan(): DateTimeSpan
    {
        return new DateTimeSpan($this->years, $this->months, $this->days);
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
     * @param DateSpan $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        return $this->getValues() === $other->getValues();
    }

    /**
     * @param DateSpan $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        return $this->years <=> $other->years
            ?: $this->months <=> $other->months
            ?: $this->days <=> $other->days;
    }

    public function isZero(): bool
    {
        return $this->years === 0
            && $this->months === 0
            && $this->days === 0;
    }

    public function isMixed(): bool
    {
        return !$this->isPositive() && !$this->isNegative();
    }

    private function isPositive(): bool
    {
        return $this->years >= 0
            && $this->months >= 0
            && $this->days >= 0;
    }

    private function isNegative(): bool
    {
        return $this->years < 0
            && $this->months < 0
            && $this->days < 0;
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function add(self ...$other): self
    {
        $that = clone($this);
        foreach ($other as $span) {
            $that->years += $span->years;
            $that->months += $span->months;
            $that->days += $span->days;
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
        return new self(-$this->years, -$this->months, -$this->days);
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
    public function normalize(): self
    {
        $days = $this->days;
        $months = $this->months;
        $years = $this->years;

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

        return new self($years, $months, $days);
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
            + $this->days / 365;
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
            + $this->days / 30;
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
        return $this->getDays() / 7;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function getDaysTotal(): int
    {
        return $this->getDays()
            + $this->months * 30
            + $this->years * 12 * 30;
    }

}
