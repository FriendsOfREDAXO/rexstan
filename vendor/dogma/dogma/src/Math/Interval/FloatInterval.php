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
use Dogma\Check;
use Dogma\Cls;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\IntersectComparable;
use Dogma\IntersectResult;
use Dogma\InvalidValueException;
use Dogma\Obj;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use const INF;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function is_nan;
use function number_format;
use function sprintf;

class FloatInterval implements OpenClosedInterval
{
    use StrictBehaviorMixin;

    public const MIN = -INF;
    public const MAX = INF;

    /** @var float */
    private $start;

    /** @var float */
    private $end;

    /** @var bool */
    private $openStart;

    /** @var bool */
    private $openEnd;

    final public function __construct(float $start, float $end, bool $openStart = false, bool $openEnd = false)
    {
        if (is_nan($start)) {
            throw new InvalidValueException($start, Type::FLOAT);
        }
        if (is_nan($end)) {
            throw new InvalidValueException($end, Type::FLOAT);
        }
        Check::min($end, $start);

        $this->start = $start;
        $this->end = $end;
        $this->openStart = $openStart;
        $this->openEnd = $openEnd;

        if ($start === $end) {
            if ($openStart || $openEnd) {
                // default createEmpty()
                $this->start = self::MAX;
                $this->end = self::MIN;
                $this->openStart = $this->openEnd = false;
            }
        }
    }

    public static function closed(float $start, float $end): self
    {
        return new static($start, $end, false, false);
    }

    public static function open(float $start, float $end): self
    {
        return new static($start, $end, true, true);
    }

    public static function openStart(float $start, float $end): self
    {
        return new static($start, $end, true, false);
    }

    public static function openEnd(float $start, float $end): self
    {
        return new static($start, $end, false, true);
    }

    public static function empty(): self
    {
        $interval = new static(0.0, 0.0);
        $interval->start = self::MAX;
        $interval->end = self::MIN;

        return $interval;
    }

    public static function all(): self
    {
        return new static(self::MIN, self::MAX);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%s%d - %d%s #%s)',
            Cls::short(static::class),
            $this->start,
            $this->openStart ? ']' : '[',
            $this->end,
            $this->openEnd ? '[' : ']',
            Obj::dumpHash($this)
        );
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(float $byValue): self
    {
        return new static($this->start + $byValue, $this->end + $byValue, $this->openStart, $this->openEnd);
    }

    public function multiply(float $byValue): self
    {
        return new static($this->start * $byValue, $this->end * $byValue, $this->openStart, $this->openEnd);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(int $decimals = 15, string $decimalPoint = '.'): string
    {
        return sprintf(
            '%s%s, %s%s',
            $this->openStart ? '(' : '[',
            number_format($this->start, $decimals, $decimalPoint, ''),
            number_format($this->end, $decimals, $decimalPoint, ''),
            $this->openEnd ? ')' : ']'
        );
    }

    public function getStart(): float
    {
        return $this->start;
    }

    public function getEnd(): float
    {
        return $this->end;
    }

    /**
     * @return float[]
     */
    public function getStartEnd(): array
    {
        return [$this->start, $this->end];
    }

    public function getLength(): float
    {
        return $this->start > $this->end ? 0.0 : $this->end - $this->start;
    }

    public function hasOpenStart(): bool
    {
        return $this->openStart;
    }

    public function hasOpenEnd(): bool
    {
        return $this->openEnd;
    }

    public function isEmpty(): bool
    {
        return $this->start > $this->end || ($this->start === $this->end && $this->openStart && $this->openEnd);
    }

    /**
     * @param FloatInterval $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return ($this->start === $other->start && $this->openStart === $other->openStart
            && $this->end === $other->end && $this->openEnd === $other->openEnd)
            || ($this->isEmpty() && $other->isEmpty());
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start <=> $other->start ?: $this->openStart <=> $other->openStart
            ?: $this->end <=> $other->end ?: $other->openEnd <=> $this->openEnd;
    }

    /**
     * @param self $other
     * @return int
     */
    public function compareIntersects(IntersectComparable $other): int
    {
        Check::instance($other, self::class);

        if ($this->start === $other->start && $this->openStart === $other->openStart) {
            if ($this->end === $other->end && $this->openEnd === $other->openEnd) {
                return IntersectResult::SAME;
            } elseif ($this->end < $other->end || ($this->end === $other->end && $this->openEnd === true)) {
                return IntersectResult::FITS_TO_START;
            } else {
                return IntersectResult::EXTENDS_END;
            }
        } elseif ($this->start < $other->start || ($this->start === $other->start && $this->openStart === false)) {
            if ($this->end === $other->start && ($this->openEnd xor $other->openStart)) {
                return IntersectResult::TOUCHES_START;
            } elseif ($this->end < $other->start || ($this->end === $other->start && $this->openEnd === true)) {
                return IntersectResult::BEFORE_START;
            } elseif ($this->end === $other->end && $this->openEnd === $other->openEnd) {
                return IntersectResult::EXTENDS_START;
            } elseif ($this->end < $other->end || ($this->end === $other->end && $this->openEnd === true)) {
                return IntersectResult::INTERSECTS_START;
            } else {
                return IntersectResult::CONTAINS;
            }
        } else {
            if ($this->start === $other->end && ($this->openStart xor $other->openEnd)) {
                return IntersectResult::TOUCHES_END;
            } elseif ($this->start > $other->end || ($this->start === $other->end && $other->openEnd === true)) {
                return IntersectResult::AFTER_END;
            } elseif ($this->end === $other->end && $this->openEnd === $other->openEnd) {
                return IntersectResult::FITS_TO_END;
            } elseif ($this->end < $other->end || ($this->end === $other->end && $this->openEnd === true)) {
                return IntersectResult::IS_CONTAINED;
            } else {
                return IntersectResult::INTERSECTS_END;
            }
        }
    }

    public function containsValue(float $value): bool
    {
        return ($this->openStart ? $value > $this->start : $value >= $this->start)
            && ($this->openEnd ? $value < $this->end : $value <= $this->end);
    }

    public function contains(self $interval): bool
    {
        return !$interval->isEmpty()
            && (($this->openStart && !$interval->openStart) ? $interval->start > $this->start : $interval->start >= $this->start)
            && (($this->openEnd && !$interval->openEnd) ? $interval->end < $this->end : $interval->end <= $this->end);
    }

    public function intersects(self $interval): bool
    {
        return $this->containsValue($interval->start)
            || $this->containsValue($interval->end)
            || $interval->containsValue($this->start)
            || $interval->containsValue($this->end)
            || ($this->start === $interval->start && $this->end === $interval->end);
    }

    public function touches(self $interval, bool $exclusive = false): bool
    {
        return ($this->start === $interval->end && ($exclusive ? ($this->openStart xor $interval->openEnd) : true))
            || ($this->end === $interval->start && ($exclusive ? ($this->openEnd xor $interval->openStart) : true));
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts, int $splitMode = self::SPLIT_OPEN_ENDS): FloatIntervalSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new FloatIntervalSet([$this]);
        }

        $partSize = ($this->end - $this->start) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            $intervalStarts[] = $this->start + $partSize * $n;
        }
        $intervalStarts = array_unique($intervalStarts);

        return $this->splitBy($intervalStarts, $splitMode);
    }

    /**
     * @param float[] $intervalStarts
     * @return FloatIntervalSet
     */
    public function splitBy(array $intervalStarts, int $splitMode = self::SPLIT_OPEN_ENDS): FloatIntervalSet
    {
        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        foreach ($intervalStarts as $intervalStart) {
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart)) {
                $results[$i] = new static($interval->start, $intervalStart, $interval->openStart, $splitMode === self::SPLIT_OPEN_ENDS ? self::OPEN : self::CLOSED);
                $results[] = new static($intervalStart, $interval->end, $splitMode === self::SPLIT_OPEN_STARTS ? self::OPEN : self::CLOSED, $interval->openEnd);
                $i++;
            }
        }

        return new FloatIntervalSet($results);
    }

    // A1****A2****B1****B2 -> [A1, B2]
    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = self::MAX;
        $end = self::MIN;
        $startExclusive = true;
        $endExclusive = true;
        foreach ($items as $item) {
            if ($item->start < $start) {
                $start = $item->start;
                $startExclusive = $item->openStart;
            } elseif ($startExclusive && !$item->openStart && $item->start === $start) {
                $startExclusive = false;
            }
            if ($item->end > $end) {
                $end = $item->end;
                $endExclusive = $item->openEnd;
            } elseif ($endExclusive && !$item->openEnd && $item->end === $end) {
                $endExclusive = false;
            }
        }

        return new static($start, $end, $startExclusive, $endExclusive);
    }

    // A1----B1****A2----B2 -> [B1, A2]
    // A1----A2    B1----B2 -> [MAX, MIN]
    public function intersect(self ...$items): self
    {
        $items[] = $this;
        /** @var self[] $items */
        $items = Arr::sortComparable($items);

        /** @var self $result */
        $result = array_shift($items);
        foreach ($items as $item) {
            if ($result->start < $item->start || ($result->start === $item->start && $result->openStart && !$item->openStart)) {
                if ($result->end < $item->start || ($result->end === $item->start && ($result->openEnd || $item->openStart))) {
                    return self::empty();
                }
                $result = new static($item->start, $result->end, $item->openStart, $result->openEnd);
            }
            if ($result->end > $item->end || ($result->end === $item->end && !$result->openEnd && $item->openEnd)) {
                if ($result->start > $item->end || ($result->start === $item->end && ($result->openStart || $item->openEnd))) {
                    return self::empty();
                }
                $result = new static($result->start, $item->end, $result->openStart, $item->openEnd);
            }
        }

        return $result;
    }

    // A1****B1****A2****B2 -> {[A1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function union(self ...$items): FloatIntervalSet
    {
        $items[] = $this;
        /** @var self[] $items */
        $items = Arr::sortComparable($items);

        /** @var FloatInterval $current */
        $current = array_shift($items);
        $results = [$current];
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($current->intersects($item)) {
                $current = $current->envelope($item);
                $results[count($results) - 1] = $current;
            } else {
                $current = $item;
                $results[] = $current;
            }
        }

        return new FloatIntervalSet($results);
    }

    // A xor B
    // A1****B1----A2****B2 -> {[A1, A2], [B1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function difference(self ...$items): FloatIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new FloatIntervalSet($results);
    }

    // A minus B
    // A1****B1----A2----B2 -> {[A1, B1]}
    // A1****A2    B1----B2 -> {[A1, A2]}
    public function subtract(self ...$items): FloatIntervalSet
    {
        $results = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            foreach ($results as $r => $interval) {
                $startLower = $interval->start < $item->start || ($interval->start === $item->start && !$interval->openStart && $item->openStart);
                $endHigher = $interval->end > $item->end || ($interval->end === $item->end && $interval->openEnd && !$item->openEnd);
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    $results[] = new static($item->end, $interval->end, !$item->openEnd, $interval->openEnd);
                } elseif ($startLower) {
                    if ($interval->end < $item->start || ($interval->end === $item->start && $interval->openEnd && !$item->openStart)) {
                        // r1****r2    i1----i2
                        continue;
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    }
                } elseif ($endHigher) {
                    if ($interval->start > $item->end || ($interval->start === $item->end && $interval->openStart && !$item->openEnd)) {
                        // i1----i2    r1****r2
                        continue;
                    } else {
                        // i1----r1----i2****r2
                        unset($results[$r]);
                        $results[] = new static($item->end, $interval->end, !$item->openEnd, $interval->openEnd);
                    }
                } else {
                    // i1----r1----r2----i2
                    unset($results[$r]);
                }
            }
        }

        return new FloatIntervalSet(array_values($results));
    }

    // All minus A
    public function invert(): FloatIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param FloatInterval ...$items
     * @return array<array{0: FloatInterval, 1: int}> ($ident => ($interval, $count))
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        foreach ($overlaps as $overlap) {
            $ident = $overlap->format();
            if (isset($results[$ident])) {
                $results[$ident][1]++;
            } else {
                $results[$ident] = [$overlap, 1];
            }
        }

        return array_values($results);
    }

    /**
     * O(n log n)
     * @param FloatInterval ...$items
     * @return FloatInterval[]
     */
    public static function explodeOverlaps(self ...$items): array
    {
        // 0-5 1-6 2-7 -->  0-1< 1-2< 1-2< 2-5 2-5 2-5 >5-6 >5-6 >6-7

        /** @var self[] $items */
        $items = Arr::sortComparable($items);
        $starts = array_fill(0, count($items), 0);
        $i = 0;
        while (isset($items[$i])) {
            $a = $items[$i];
            if ($a->isEmpty()) {
                unset($items[$i]);
                $i++;
                continue;
            }
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                } elseif ($a->end < $b->start || ($a->end === $b->start && ($a->openEnd || $b->openStart))
                    || $a->start > $b->end || ($a->start === $b->end && ($a->openStart || $b->openEnd))) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($a->start === $b->start && $a->openStart === $b->openStart) {
                    if ($a->end === $b->end && $a->openEnd === $b->openEnd) {
                        // a1=b1----a2=b2
                        continue;
                    } elseif ($a->end > $b->end || ($a->end === $b->end && $a->openEnd === false)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($a->start < $b->start || ($a->start === $b->start && $a->openStart === false)) {
                    if ($a->end === $b->end && $a->openEnd === $b->openEnd) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end > $b->end || ($a->end === $b->end && $a->openEnd === false)) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end, $b->openStart, $a->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end === $b->end && $a->openEnd === $b->openEnd) {
                        // b1----a1----a2=b2
                        continue;
                    } elseif ($a->end > $b->end || ($a->end === $b->end && $a->openEnd === false)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end, $a->openStart, $b->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2----b2
                        continue;
                    }
                }
            }
            $i++;
        }

        return array_values(Arr::sortComparable($items));
    }

    /**
     * @param self[] $intervals
     * @return self[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $intervals): array
    {
        return Arr::sortComparable($intervals);
    }

    /**
     * @param self[] $intervals
     * @return self[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sortByStart(array $intervals): array
    {
        return Arr::sortComparable($intervals);
    }

}
