<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\Arr;
use Dogma\ArrayIterator;
use Dogma\Check;
use Dogma\Compare;
use Dogma\Equalable;
use Dogma\Math\Interval\IntervalSet;
use Dogma\Math\Interval\IntervalSetDumpMixin;
use Dogma\Pokeable;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Traversable;
use function array_map;
use function array_merge;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function reset;
use function sort;

/**
 * @implements IntervalSet<NightInterval>
 */
class NightIntervalSet implements IntervalSet, DateOrTimeIntervalSet, Pokeable
{
    use StrictBehaviorMixin;
    use IntervalSetDumpMixin;

    /** @var NightInterval[] */
    private $intervals;

    /**
     * @param NightInterval[] $intervals
     */
    final public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, static function (NightInterval $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    public static function createFromDateIntervalSet(DateIntervalSet $set): self
    {
        return new static(Arr::map($set->getIntervals(), static function (DateInterval $interval): NightInterval {
            return NightInterval::createFromDateInterval($interval);
        }));
    }

    /**
     * @param Date[] $dates
     * @return NightIntervalSet
     */
    public static function createFromDateArray(array $dates): self
    {
        if (count($dates) === 0) {
            return new static([]);
        }

        sort($dates);
        $intervals = [];
        $start = $previous = reset($dates);
        foreach ($dates as $date) {
            if ($date->getJulianDay() > $previous->getJulianDay() + 1) {
                $intervals[] = new NightInterval($start, $previous->addDay());
                $start = $date;
            }
            $previous = $date;
        }
        if ($start !== false) {
            $intervals[] = new NightInterval($start, $previous->addDay());
        }

        return new static($intervals);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        foreach ($this->intervals as $interval) {
            $interval->poke();
        }
    }

    public function toDateIntervalSet(): DateIntervalSet
    {
        return new DateIntervalSet(Arr::map($this->intervals, static function (NightInterval $interval): DateInterval {
            return $interval->toDateInterval();
        }));
    }

    /**
     * @return Date[]
     */
    public function toDateArray(): array
    {
        return array_merge(...array_map(static function (NightInterval $interval) {
            return $interval->toDateArray();
        }, $this->intervals));
    }

    public function format(string $format = NightInterval::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        return implode(', ', Arr::map($this->intervals, static function (NightInterval $interval) use ($format, $formatter): string {
            return $interval->format($format, $formatter);
        }));
    }

    /**
     * @return NightInterval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    /**
     * @return Traversable<NightInterval>
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

    public function containsValue(Date $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function containsInterval(NightInterval $interval): bool
    {
        foreach ($this->intervals as $int) {
            if ($int->contains($interval)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): NightInterval
    {
        if ($this->intervals === []) {
            return NightInterval::empty();
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
        /** @var NightInterval[] $intervals */
        $intervals = Arr::sortComparableValues($this->intervals);
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

    public function addIntervals(NightInterval ...$intervals): self
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

    public function subtractIntervals(NightInterval ...$intervals): self
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

        /** @var NightInterval[] $results */
        $results = $results;

        return new static($results);
    }

    public function invert(): self
    {
        return (new static([NightInterval::all()]))->subtract($this);
    }

    /**
     * Intersect with another set of intervals.
     * @return self
     */
    public function intersect(self $set): self
    {
        return $this->intersectIntervals(...$set->intervals);
    }

    public function intersectIntervals(NightInterval ...$intervals): self
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

    public function filterByLength(string $operator, int $days): self
    {
        return $this->filterByNightsCount($operator, $days);
    }

    public function filterByNightsCount(string $operator, int $days): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $interval->getLengthInDays() <=> $days;
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
            if ($result instanceof NightInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } else {
                throw new ShouldNotHappenException('Expected NightInterval or NightIntervalSet or array of NightIntervals.');
            }
        }

        return new static($results);
    }

    public function collect(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof NightInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } elseif ($result === null) {
                continue;
            } else {
                throw new ShouldNotHappenException('Expected NightInterval or NightIntervalSet or array of NightIntervals.');
            }
        }

        return new static($results);
    }

}
