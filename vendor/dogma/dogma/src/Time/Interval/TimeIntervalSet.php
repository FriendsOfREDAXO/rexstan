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
use Dogma\Math\Interval\IntervalSetDumpMixin;
use Dogma\Math\Interval\ModuloIntervalSet;
use Dogma\Pokeable;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Time;
use Traversable;
use function array_merge;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function reset;

/**
 * @implements ModuloIntervalSet<TimeInterval>
 */
class TimeIntervalSet implements ModuloIntervalSet, DateOrTimeIntervalSet, Pokeable
{
    use StrictBehaviorMixin;
    use IntervalSetDumpMixin;

    /** @var TimeInterval[] */
    private $intervals;

    /**
     * @param TimeInterval[] $intervals
     */
    final public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, static function (TimeInterval $interval): bool {
            return !$interval->isEmpty();
        }));
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

    public function format(
        string $format = TimeInterval::DEFAULT_FORMAT,
        ?DateTimeIntervalFormatter $formatter = null
    ): string
    {
        return implode(', ', Arr::map($this->intervals, static function (TimeInterval $timeInterval) use ($format, $formatter): string {
            return $timeInterval->format($format, $formatter);
        }));
    }

    /**
     * @return TimeInterval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    /**
     * @return Traversable<TimeInterval>
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

    public function containsValue(Time $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function containsInterval(TimeInterval $interval): bool
    {
        foreach ($this->intervals as $int) {
            if ($int->contains($interval)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): TimeInterval
    {
        if ($this->intervals === []) {
            return TimeInterval::empty();
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
        /** @var TimeInterval[] $intervals */
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

    public function addIntervals(TimeInterval ...$intervals): self
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

    public function subtractIntervals(TimeInterval ...$intervals): self
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

        /** @var TimeInterval[] $results */
        $results = $results;

        return new static($results);
    }

    public function invert(): self
    {
        return (new static([TimeInterval::all()]))->subtract($this);
    }

    /**
     * Intersect with another set of intervals.
     * @return self
     */
    public function intersect(self $set): self
    {
        return $this->intersectIntervals(...$set->intervals);
    }

    public function intersectIntervals(TimeInterval ...$intervals): self
    {
        $results = [];
        foreach ($this->intervals as $result) {
            foreach ($intervals as $interval) {
                if ($result->intersects($interval)) {
                    $results = array_merge($results, $result->intersect($interval)->getIntervals());
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
            if ($result instanceof TimeInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } else {
                throw new ShouldNotHappenException('Expected TimeInterval or TimeIntervalSet or array of TimeIntervals.');
            }
        }

        return new static($results);
    }

    public function collect(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof TimeInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } elseif ($result === null) {
                continue;
            } else {
                throw new ShouldNotHappenException('Expected TimeInterval or TimeIntervalSet or array of TimeIntervals.');
            }
        }

        return new static($results);
    }

}
