<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use DateTimeZone;
use Dogma\Arr;
use Dogma\ArrayIterator;
use Dogma\Check;
use Dogma\Compare;
use Dogma\Equalable;
use Dogma\Math\Interval\IntervalSet;
use Dogma\Math\Interval\IntervalSetDumpMixin;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Traversable;
use function array_merge;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function reset;

/**
 * @implements IntervalSet<DateTimeInterval>
 */
class DateTimeIntervalSet implements IntervalSet, DateOrTimeIntervalSet
{
    use StrictBehaviorMixin;
    use IntervalSetDumpMixin;

    /** @var DateTimeInterval[] */
    private $intervals;

    /**
     * @param DateTimeInterval[] $intervals
     */
    final public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, static function (DateTimeInterval $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    public static function createFromDateAndTimeIntervalSet(
        Date $date,
        TimeIntervalSet $timeIntervalSet,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($timeIntervalSet->getIntervals() as $timeInterval) {
            $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
        }

        return new static($intervals);
    }

    public static function createFromDateIntervalAndTimeInterval(
        DateInterval $dateInterval,
        TimeInterval $timeInterval,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($dateInterval->toDateArray() as $date) {
            $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
        }

        return new static($intervals);
    }

    public static function createFromDateIntervalAndTimeIntervalSet(
        DateInterval $dateInterval,
        TimeIntervalSet $timeIntervalSet,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($dateInterval->toDateArray() as $date) {
            foreach ($timeIntervalSet->getIntervals() as $timeInterval) {
                $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
            }
        }

        return new static($intervals);
    }

    public static function createFromDateIntervalSetAndTimeInterval(
        DateIntervalSet $dateIntervalSet,
        TimeInterval $timeInterval,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($dateIntervalSet->getIntervals() as $dateInterval) {
            foreach ($dateInterval->toDateArray() as $date) {
                $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
            }
        }

        return new static($intervals);
    }

    public static function createFromDateIntervalSetAndTimeIntervalSet(
        DateIntervalSet $dateIntervalSet,
        TimeIntervalSet $timeIntervalSet,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($dateIntervalSet->getIntervals() as $dateInterval) {
            foreach ($dateInterval->toDateArray() as $date) {
                foreach ($timeIntervalSet->getIntervals() as $timeInterval) {
                    $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
                }
            }
        }

        return new static($intervals);
    }

    public static function createFromDateIntervalAndWeekDayHoursSet(
        DateInterval $dateInterval,
        WeekDayHoursSet $weekDayHoursSet,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($dateInterval->toDateArray() as $date) {
            $weekDayHours = $weekDayHoursSet->getByDayNumber($date->getDayOfWeek());
            if ($weekDayHours === null) {
                continue;
            }
            foreach ($weekDayHours->getTimeIntervals() as $timeInterval) {
                $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
            }
        }

        return new static($intervals);
    }

    public static function createFromDateIntervalSetAndWeekDayHoursSet(
        DateIntervalSet $dateIntervalSet,
        WeekDayHoursSet $weekDayHoursSet,
        ?DateTimeZone $timeZone = null
    ): self
    {
        $intervals = [];
        foreach ($dateIntervalSet->getIntervals() as $dateInterval) {
            foreach ($dateInterval->toDateArray() as $date) {
                $weekDayHours = $weekDayHoursSet->getByDayNumber($date->getDayOfWeek());
                if ($weekDayHours === null) {
                    continue;
                }
                foreach ($weekDayHours->getTimeIntervals() as $timeInterval) {
                    $intervals[] = DateTimeInterval::createFromDateAndTimeInterval($date, $timeInterval, $timeZone);
                }
            }
        }

        return new static($intervals);
    }

    public function format(
        string $format = DateTimeInterval::DEFAULT_FORMAT,
        ?DateTimeIntervalFormatter $formatter = null
    ): string
    {
        return implode(', ', Arr::map($this->intervals, static function (DateTimeInterval $dateTimeInterval) use ($format, $formatter): string {
            return $dateTimeInterval->format($format, $formatter);
        }));
    }

    /**
     * @return DateTimeInterval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    /**
     * @return Traversable<DateTimeInterval>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->intervals);
    }

    public function isEmpty(): bool
    {
        return $this->intervals === [];
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        $otherIntervals = $other->getIntervals();
        if (count($this->intervals) !== count($otherIntervals)) {
            return false;
        }
        foreach ($this->intervals as $i => $interval) {
            if (!$interval->equals($otherIntervals[$i])) {
                return false;
            }
        }

        return true;
    }

    public function containsValue(DateTime $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function containsInterval(DateTimeInterval $interval): bool
    {
        foreach ($this->intervals as $int) {
            if ($int->contains($interval)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): DateTimeInterval
    {
        if ($this->intervals === []) {
            return DateTimeInterval::empty();
        } else {
            return reset($this->intervals)->envelope(...$this->intervals);
        }
    }

    /**
     * Join overlapping intervals in set.
     * @return self
     */
    public function normalize(): self
    {
        /** @var DateTimeInterval[] $intervals */
        $intervals = Arr::sortComparable($this->intervals);
        $count = count($intervals) - 1;
        for ($n = 0; $n < $count; $n++) {
            if ($intervals[$n]->intersects($intervals[$n + 1]) || $intervals[$n]->touches($intervals[$n + 1])) {
                $intervals[$n + 1] = $intervals[$n]->envelope($intervals[$n + 1]);
                unset($intervals[$n]);
            }
        }

        return new static($intervals);
    }

    /**
     * Add another set of intervals to this one without normalization.
     * @return self
     */
    public function add(self $set): self
    {
        return $this->addIntervals(...$set->intervals);
    }

    public function addIntervals(DateTimeInterval ...$intervals): self
    {
        return new static(array_merge($this->intervals, $intervals));
    }

    /**
     * Remove another set of intervals from this one.
     * @return self
     */
    public function subtract(self $set): self
    {
        return $this->subtractIntervals(...$set->intervals);
    }

    public function subtractIntervals(DateTimeInterval ...$intervals): self
    {
        $sources = $this->intervals;
        $results = [];
        while ($result = array_shift($sources)) {
            foreach ($intervals as $interval) {
                $result = $result->subtract($interval);
                if (count($result->intervals) === 0) {
                    continue 2;
                } elseif (count($result->intervals) === 2) {
                    $sources[] = $result->intervals[1];
                }
                $result = $result->intervals[0];
            }
            if (!$result->isEmpty()) {
                $results[] = $result;
            }
        }

        /** @var DateTimeInterval[] $results */
        $results = $results;

        return new static($results);
    }

    /**
     * Intersect with another set of intervals.
     * @return self
     */
    public function intersect(self $set): self
    {
        return $this->intersectIntervals(...$set->intervals);
    }

    public function intersectIntervals(DateTimeInterval ...$intervals): self
    {
        $results = [];
        foreach ($this->intervals as $result) {
            foreach ($intervals as $interval) {
                if ($result->intersects($interval)) {
                    $results[] = $result->intersect($interval);
                }
            }
        }

        return new static($results);
    }

    public function filterByLength(string $operator, int $microseconds): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $interval->getLengthInMicroseconds() <=> $microseconds;
            switch ($operator) {
                case Compare::LESSER:
                    if ($result === -1) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::LESSER_OR_EQUAL:
                    if ($result !== 1) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::EQUAL:
                    if ($result === 0) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::NOT_EQUAL:
                    if ($result !== 0) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::GREATER_OR_EQUAL:
                    if ($result !== -1) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::GREATER:
                    if ($result === 1) {
                        $results[] = $interval;
                    }
                    break;
            }
        }

        return new static($results);
    }

    public function map(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof DateTimeInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } else {
                throw new ShouldNotHappenException('Expected DateTimeInterval or DateTimeIntervalSet or array of DateTimeIntervals.');
            }
        }

        return new static($results);
    }

    public function collect(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof DateTimeInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } elseif ($result === null) {
                continue;
            } else {
                throw new ShouldNotHappenException('Expected DateTimeInterval or DateTimeIntervalSet or array of DateTimeIntervals.');
            }
        }

        return new static($results);
    }

}
