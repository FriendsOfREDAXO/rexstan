<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use DateTimeInterface;
use Dogma\Arr;
use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\Math\Interval\IntervalDumpMixin;
use Dogma\Math\Interval\IntervalParser;
use Dogma\Math\Interval\ModuloInterval;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\Microseconds;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Span\TimeSpan;
use Dogma\Time\Time;
use function array_fill;
use function array_map;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function max;
use function min;
use function round;

/**
 * Interval of times without date.
 *
 * Start time (and end time in the same day) is automatically normalized to 00:00-23:59.
 * End time if it is after midnight (that means lower value than start time), will be automatically normalized to 24:00-47:59.
 *
 * Span between start and end of interval cannot be more than 24 hours.
 */
class TimeInterval implements ModuloInterval, DateOrTimeInterval, Pokeable
{
    use StrictBehaviorMixin;
    use IntervalDumpMixin;

    public const MIN = Time::MIN;
    public const MAX = '47:59:59.999999';

    public const DEFAULT_FORMAT = 'H:i:s.u| - H:i:s.u';

    /** @var Time */
    private $start;

    /** @var Time */
    private $end;

    final public function __construct(Time $start, Time $end)
    {
        $startTime = $start->getMicroTime();
        $endTime = $end->getMicroTime();

        if ($startTime >= Microseconds::DAY) {
            $startTime %= Microseconds::DAY;
            $start = $start->normalize();
        }
        if ($endTime > Microseconds::DAY) {
            $endTime %= Microseconds::DAY;
            $end = $end->normalize();
        }
        if ($startTime > $endTime) {
            $end = $end->denormalize();
        }

        $this->start = $start;
        $this->end = $end;
    }

    public static function createFromString(string $string): self
    {
        [$start, $end] = IntervalParser::parseString($string);

        $start = new Time($start);
        $end = new Time($end);

        return new static($start, $end);
    }

    public static function createFromStartAndLength(Time $start, DateTimeUnit $unit, int $amount): self
    {
        if (!$unit->isTime()) {
            throw new InvalidDateTimeUnitException($unit);
        }
        if ($unit->equalsValue(DateTimeUnit::MILISECOND)) {
            $unit = DateTimeUnit::microsecond();
            $amount *= 1000;
        }

        return new static($start, $start->modify('+' . $amount . ' ' . $unit->getValue()));
    }

    public static function createFromDateTimeInterfaces(DateTimeInterface $start, DateTimeInterface $end): self
    {
        return new static(Time::createFromDateTimeInterface($start), Time::createFromDateTimeInterface($end));
    }

    public static function closed(Time $start, Time $end): self
    {
        return new static($start, $end);
    }

    public static function empty(): self
    {
        return new static(new Time(self::MIN), new Time(self::MIN));
    }

    public static function all(): self
    {
        return new static(new Time(self::MIN), new Time(self::MAX));
    }

    public function normalize(): self
    {
        if ($this->start->isNormalized()) {
            $self = new static($this->start, $this->end);
            $self->start = $this->start->normalize();
            $self->end = $this->end->normalize();

            return $self;
        } else {
            return $this;
        }
    }

    public function denormalize(): self
    {
        if ($this->end->isNormalized()) {
            $self = new static($this->start, $this->end);
            $self->start = $this->start->denormalize();
            $self->end = $this->end->denormalize();

            return $self;
        } else {
            return $this;
        }
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        $this->start->format();
        $this->end->format();
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value));
    }

    public function setStart(Time $start): self
    {
        return new static($start, $this->end);
    }

    public function setEnd(Time $end): self
    {
        return new static($this->start, $end);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getTimeSpan(): TimeSpan
    {
        return TimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getLengthInMicroseconds(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getMicroTime() - $this->start->getMicroTime();
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this, $format);
    }

    public function getStart(): Time
    {
        return $this->start;
    }

    public function getEnd(): Time
    {
        return $this->end;
    }

    /**
     * @return Time[]
     */
    public function getStartEnd(): array
    {
        return [$this->start, $this->end];
    }

    public function isEmpty(): bool
    {
        return $this->start->getMicroTime() === $this->end->getMicroTime();
    }

    public function isOverMidnight(): bool
    {
        return $this->end->getMicroTime() >= Time::MAX_MICROSECONDS;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->start->equals($other->start)
            && $this->end->getMicroTime() === $other->end->getMicroTime(); // cannot use Time::equals() because of 00:00 vs 24:00
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start)
            ?: $this->end->getMicroTime() <=> $other->end->getMicroTime(); // cannot use Time::compare() because of 00:00 vs 24:00
    }

    public function containsValue(Time $value): bool
    {
        $time = $value->normalize()->getMicroTime();
        $time2 = $value->denormalize()->getMicroTime();
        $startTime = $this->getStart()->getMicroTime();
        $endTime = $this->getEnd()->getMicroTime();

        return ($time >= $startTime && $time < $endTime) || ($time2 >= $startTime && $time2 < $endTime);
    }

    /**
     * @param TimeInterval $interval
     * @return bool
     */
    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        $intervalStart = $interval->start->getMicroTime();
        $intervalEnd = $interval->getEnd()->getMicroTime();
        $thisStart = $this->getStart()->getMicroTime();
        $thisEnd = $this->getEnd()->getMicroTime();

        return $intervalStart >= $thisStart && $intervalEnd <= $thisEnd;
    }

    public function intersects(self $interval): bool
    {
        return $this->containsValue($interval->start)
            || $this->containsValue($interval->end)
            || $interval->containsValue($this->start)
            || $interval->containsValue($this->end)
            || ($this->start->equals($interval->start) && $this->end->equals($interval->end));
    }

    /**
     * @param TimeInterval $interval
     * @return bool
     */
    public function touches(self $interval): bool
    {
        return ($this->start->getMicroTime() === $interval->end->getMicroTime())
            || ($this->end->getMicroTime() === $interval->start->getMicroTime());
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): TimeIntervalSet
    {
        if ($this->isEmpty()) {
            return new TimeIntervalSet([]);
        }

        $partSize = ($this->end->getMicroTime() - $this->start->getMicroTime()) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            // rounded to microseconds
            $intervalStarts[] = round(($this->start->getMicroTime() + $partSize * $n) % (Time::MAX_MICROSECONDS + 1), 6);
        }
        $intervalStarts = array_unique($intervalStarts);
        $intervalStarts = Arr::map($intervalStarts, static function (int $timestamp): Time {
            return new Time($timestamp);
        });

        return $this->splitBy($intervalStarts);
    }

    /**
     * @param Time[] $intervalStarts
     * @return TimeIntervalSet
     */
    public function splitBy(array $intervalStarts): TimeIntervalSet
    {
        if ($this->isEmpty()) {
            return new TimeIntervalSet([]);
        }

        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        foreach ($intervalStarts as $intervalStart) {
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart)) {
                $results[$i] = new static($interval->start, $intervalStart);
                $results[] = new static($intervalStart, $interval->end);
                $i++;
            }
        }

        return new TimeIntervalSet($results);
    }

    /**
     * @return self[]
     */
    public function splitByMidnight(): array
    {
        if (!$this->isOverMidnight()) {
            return [$this, self::empty()];
        }

        return [
            new self($this->start, new Time(Time::MAX_MICROSECONDS)),
            new self(new Time(Time::MIN_MICROSECONDS), $this->end),
        ];
    }

    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = new Time(self::MAX);
        $end = new Time(self::MIN);
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($item->start->getMicroTime() < $start->getMicroTime()) {
                $start = $item->start;
            }
            if ($item->end->getMicroTime() > $end->getMicroTime()) {
                $end = $item->end;
            }
        }

        return new static($start, $end);
    }

    public function intersect(self ...$items): TimeIntervalSet
    {
        $items[] = $this;
        $items = array_map(static function (self $interval): array {
            if ($interval->isOverMidnight()) {
                $a = $interval->getStart()->getMicroTime();
                $b = Microseconds::DAY;
                $c = Microseconds::DAY;
                $d = $interval->getEnd()->getMicroTime();
            } else {
                $a = $interval->getStart()->getMicroTime();
                $b = $interval->getEnd()->getMicroTime();
                $c = $a + Microseconds::DAY;
                $d = $b + Microseconds::DAY;
            }
            return [$a, $b, $c, $d];
        }, $items);

        [$a, $b, $c, $d] = array_shift($items);
        foreach ($items as [$e, $f, $g, $h]) {
            $a = max($a, $e);
            $b = min($b, $f);
            $c = max($c, $g);
            $d = min($d, $h);
        }

        $result1 = $a <= $b ? new static(new Time($a), new Time($b)) : static::empty();
        $result2 = $c <= $d ? new static(new Time($c), new Time($d)) : static::empty();

        return (new TimeIntervalSet([$result1, $result2]))->normalize();
    }

    public function union(self ...$items): TimeIntervalSet
    {
        $items[] = $this;
        /** @var self[] $items */
        $items = Arr::sortComparable($items);

        /** @var TimeInterval $current */
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

        return new TimeIntervalSet($results);
    }

    public function difference(self ...$items): TimeIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new TimeIntervalSet($results);
    }

    public function subtract(self ...$items): TimeIntervalSet
    {
        $results = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            $itemStartTime = $item->getStart()->getMicroTime();
            $itemEndTime = $item->getEnd()->getMicroTime();
            foreach ($results as $r => $interval) {
                $intervalStartTime = $interval->getStart()->getMicroTime();
                $intervalEndTime = $interval->getEnd()->getMicroTime();

                $startLower = $intervalStartTime < $itemStartTime;
                $endHigher = $intervalEndTime > $itemEndTime;
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($interval->start, $item->start);
                    $results[] = new static($item->end, $interval->end);
                } elseif ($startLower) {
                    if ($intervalEndTime < $itemStartTime) {
                        // r1****r2    i1----i2
                        continue;
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start);
                    }
                } elseif ($endHigher) {
                    if ($intervalStartTime > $itemEndTime) {
                        // i1----i2    r1****r2
                        continue;
                    } else {
                        // i1----r1----i2****r2
                        unset($results[$r]);
                        $results[] = new static($item->end, $interval->end);
                    }
                } else {
                    // i1----r1----r2----i2
                    unset($results[$r]);
                }
            }
        }

        return new TimeIntervalSet(array_values($results));
    }

    public function invert(): TimeIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param TimeInterval ...$items
     * @return array<array{0: TimeInterval, 1: int}> ($interval, $count)
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
     * @param TimeInterval ...$items
     * @return TimeInterval[]
     */
    public static function explodeOverlaps(self ...$items): array
    {
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
            $aStartTime = $a->getStart()->getMicroTime();
            $aEndTime = $a->getEnd()->getMicroTime();
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                }
                $bStartTime = $b->getStart()->getMicroTime();
                $bEndTime = $b->getEnd()->getMicroTime();
                if ($aEndTime <= $bStartTime || $aStartTime >= $bEndTime) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($aStartTime === $bStartTime) {
                    if ($aEndTime > $bEndTime) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } else {
                        // a1=b1----a2=b2
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($aStartTime < $bStartTime) {
                    if ($aEndTime === $bEndTime) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } elseif ($aEndTime > $bEndTime) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    }
                } else {
                    if ($aEndTime > $bEndTime) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } else {
                        // b1----a1----a2=b2
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
