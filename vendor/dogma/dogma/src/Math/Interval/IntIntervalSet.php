<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Arr;
use Dogma\ArrayIterator;
use Dogma\Check;
use Dogma\Compare;
use Dogma\Equalable;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Traversable;
use function array_merge;
use function array_shift;
use function count;
use function end;
use function is_array;

/**
 * @implements IntervalSet<int>
 */
class IntIntervalSet implements IntervalSet
{
    use StrictBehaviorMixin;
    use IntervalSetDumpMixin;

    /** @var IntInterval[] */
    private $intervals = [];

    /**
     * @param IntInterval[] $intervals
     */
    final public function __construct(array $intervals)
    {
        Check::itemsOfType($intervals, IntInterval::class);

        foreach ($intervals as $interval) {
            if (!$interval->isEmpty()) {
                $this->intervals[] = $interval;
            }
        }
    }

    /**
     * @return IntInterval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    /**
     * @return Traversable<int>
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

    public function containsValue(int $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function containsInterval(IntInterval $interval): bool
    {
        foreach ($this->intervals as $int) {
            if ($int->contains($interval)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): IntInterval
    {
        if ($this->intervals === []) {
            return IntInterval::empty();
        } else {
            return end($this->intervals)->envelope(...$this->intervals);
        }
    }

    /**
     * Join overlapping intervals in set.
     * @return self
     */
    public function normalize(): self
    {
        /** @var IntInterval[] $intervals */
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

    public function addIntervals(IntInterval ...$intervals): self
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

    public function subtractIntervals(IntInterval ...$intervals): self
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

        /** @var IntInterval[] $results */
        $results = $results;

        return new static($results);
    }

    public function invert(): self
    {
        return (new static([IntInterval::all()]))->subtract($this);
    }

    /**
     * Intersect with another set of intervals.
     * @return self
     */
    public function intersect(self $set): self
    {
        return $this->intersectIntervals(...$set->intervals);
    }

    public function intersectIntervals(IntInterval ...$intervals): self
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

    public function filterByLength(string $operator, int $length): self
    {
        return $this->filterByCount($operator, $length + 1);
    }

    public function filterByCount(string $operator, int $count): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $interval->getCount() <=> $count;
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
            if ($result instanceof IntInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } else {
                throw new ShouldNotHappenException('Expected IntInterval or IntIntervalSet or array of IntIntervals.');
            }
        }

        return new static($results);
    }

    public function collect(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof IntInterval) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } elseif ($result === null) {
                continue;
            } else {
                throw new ShouldNotHappenException('Expected IntInterval or IntIntervalSet or array of IntIntervals.');
            }
        }

        return new static($results);
    }

}
