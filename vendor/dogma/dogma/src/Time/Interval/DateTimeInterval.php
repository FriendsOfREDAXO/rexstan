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
use DateTimeZone;
use Dogma\Arr;
use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\IntersectComparable;
use Dogma\Math\IntCalc;
use Dogma\Math\Interval\Interval;
use Dogma\Math\Interval\IntervalCalc;
use Dogma\Math\Interval\IntervalDumpMixin;
use Dogma\Math\Interval\IntervalParser;
use Dogma\NotImplementedException;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Time;
use Dogma\Time\TimeCalc;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function floor;
use function range;

/**
 * Interval of times including date.
 */
class DateTimeInterval implements Interval, DateOrTimeInterval
{
    use StrictBehaviorMixin;
    use IntervalDumpMixin;

    public const MIN = DateTime::MIN;
    public const MAX = DateTime::MAX;

    public const DEFAULT_FORMAT = 'Y-m-d H:i:s| - Y-m-d H:i:s';

    /** @var DateTime */
    private $start;

    /** @var DateTime */
    private $end;

    final public function __construct(DateTime $start, DateTime $end)
    {
        if ($start > $end) {
            throw new InvalidIntervalStartEndOrderException($start, $end);
        }

        $this->start = $start;
        $this->end = $end;

        if ($start->equals($end)) {
            // default createEmpty()
            $this->start = new DateTime(self::MAX);
            $this->end = new DateTime(self::MIN);
        }
    }

    public static function createFromString(string $string): self
    {
        [$start, $end] = IntervalParser::parseString($string);

        $start = new DateTime($start);
        $end = new DateTime($end);

        return new static($start, $end);
    }

    public static function createFromStartAndLength(DateTime $start, DateTimeUnit $unit, int $amount): self
    {
        if ($unit->equalsValue(DateTimeUnit::QUARTER)) {
            $unit = DateTimeUnit::month();
            $amount *= 3;
        } elseif ($unit->equalsValue(DateTimeUnit::MILISECOND)) {
            $unit = DateTimeUnit::microsecond();
            $amount *= 1000;
        }

        return new static($start, $start->modify('+' . $amount . ' ' . $unit->getValue()));
    }

    public static function createFromDateTimeInterfaces(DateTimeInterface $start, DateTimeInterface $end): self
    {
        return new static(DateTime::createFromDateTimeInterface($start), DateTime::createFromDateTimeInterface($end));
    }

    public static function createFromDateAndTimeInterval(Date $date, TimeInterval $timeInterval, ?DateTimeZone $timeZone = null): self
    {
        return new static(
            DateTime::createFromDateAndTime($date, $timeInterval->getStart(), $timeZone),
            DateTime::createFromDateAndTime($date, $timeInterval->getEnd(), $timeZone)
        );
    }

    public static function createFromDateIntervalAndTime(DateInterval $dateInterval, Time $time, ?DateTimeZone $timeZone = null): self
    {
        return new static(
            DateTime::createFromDateAndTime($dateInterval->getStart(), $time, $timeZone),
            DateTime::createFromDateAndTime($dateInterval->getEnd(), $time, $timeZone)
        );
    }

    public static function future(?DateTimeZone $timeZone = null, ?TimeProvider $timeProvider = null): self
    {
        $now = $timeProvider !== null ? $timeProvider->getDateTime($timeZone) : new DateTime('now', $timeZone);

        return new static($now, new DateTime(self::MAX, $timeZone));
    }

    public static function past(?DateTimeZone $timeZone = null, ?TimeProvider $timeProvider = null): self
    {
        $now = $timeProvider !== null ? $timeProvider->getDateTime($timeZone) : new DateTime('now', $timeZone);

        return new static(new DateTime(self::MIN, $timeZone), $now);
    }

    public static function empty(): self
    {
        $interval = new static(new DateTime(), new DateTime());
        $interval->start = new DateTime(self::MAX);
        $interval->end = new DateTime(self::MIN);

        return $interval;
    }

    public static function all(): self
    {
        return new static(new DateTime(self::MIN), new DateTime(self::MAX));
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value));
    }

    public function setStart(DateTime $start): self
    {
        return new static($start, $this->end);
    }

    public function setEnd(DateTime $end): self
    {
        return new static($this->start, $end);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function toDateInterval(): DateInterval
    {
        if ($this->isEmpty()) {
            return DateInterval::empty();
        }

        $start = $this->start->getTime()->getMicroTime() === Time::MAX_MICROSECONDS
            ? $this->start->getDate()->addDay()
            : $this->start->getDate();
        $end = $this->end->getTime()->getMicroTime() === Time::MIN_MICROSECONDS
            ? $this->end->getDate()->subtractDay()
            : $this->end->getDate();

        return new DateInterval($start, $end);
    }

    public function getLengthInMicroseconds(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getMicroTimestamp() - $this->start->getMicroTimestamp();
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this, $format);
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * @return DateTime[]
     */
    public function getStartEnd(): array
    {
        return [$this->start, $this->end];
    }

    public function isEmpty(): bool
    {
        return $this->start > $this->end;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return ($this->start->equals($other->start) && $this->end->equals($other->end))
            || ($this->isEmpty() && $other->isEmpty());
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start) ?: $this->end->compare($other->end);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compareIntersects(IntersectComparable $other): int
    {
        Check::instance($other, self::class);

        return IntervalCalc::compareIntersects(
            $this->start->getMicroTimestamp(),
            $this->end->getMicroTimestamp(),
            $other->start->getMicroTimestamp(),
            $other->end->getMicroTimestamp()
        );
    }

    public function containsValue(DateTime $value): bool
    {
        return $value >= $this->start && $value < $this->end;
    }

    public function containsDateTime(DateTimeInterface $value): bool
    {
        return $this->containsValue(DateTime::createFromDateTimeInterface($value));
    }

    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }
        return $interval->start >= $this->start && $interval->end <= $this->end;
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
     * @param DateTimeInterval $interval
     * @return bool
     */
    public function touches(self $interval): bool
    {
        return ($this->start->getMicroTimestamp() === $interval->end->getMicroTimestamp())
            || ($this->end->getMicroTimestamp() === $interval->start->getMicroTimestamp());
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): DateTimeIntervalSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new DateTimeIntervalSet([]);
        }

        $partSize = ($this->end->getMicroTimestamp() - $this->start->getMicroTimestamp() + 1) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            // rounded to microseconds
            $intervalStarts[] = floor($this->start->getMicroTimestamp() + $partSize * $n);
        }
        $intervalStarts = array_unique($intervalStarts); /// why unique???
        $intervalStarts = Arr::map($intervalStarts, function (int $timestamp) {
            return DateTime::createFromMicroTimestamp($timestamp, $this->getStart()->getTimezone());
        });

        return $this->splitBy($intervalStarts);
    }

    /**
     * @param DateTime[] $intervalStarts
     * @return DateTimeIntervalSet
     */
    public function splitBy(array $intervalStarts): DateTimeIntervalSet
    {
        if ($this->isEmpty()) {
            return new DateTimeIntervalSet([]);
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

        return new DateTimeIntervalSet($results);
    }

    /**
     * Splits interval into smaller by increments of given unit from the beginning of interval.
     *
     * @return DateTimeIntervalSet
     */
    public function splitByUnit(DateTimeUnit $unit, int $amount = 1): DateTimeIntervalSet
    {
        Check::positive($amount);

        $intervalStarts = [];
        $start = $this->start->addUnit($unit, $amount);
        while ($this->containsValue($start)) {
            $intervalStarts[] = $start;
            $start = $start->addUnit($unit, $amount);
        }

        return $this->splitBy($intervalStarts);
    }

    /**
     * Splits interval into parts with borders aligned to given reference or to a beginning of splitting unit.
     * e.g. [2018-01-15 - 2018-02-15] split by 1 month will return two intervals:
     *  [2018-01-15 - 2018-01-31] and [2018-02-01 - 2018-02-15]
     *
     * When no reference is given, base for splitting will be calculated by rounding given unit* to a number divisible by given amount.
     * *) in context of a superior unit - number of month in year, iso number of week in year, number of day in month...
     *  e.g. for 5 months beginning of May or October will be used as base.
     *
     * @return DateTimeIntervalSet
     */
    public function splitByUnitAligned(DateTimeUnit $unit, int $amount = 1, ?DateTime $reference = null): DateTimeIntervalSet
    {
        Check::positive($amount);

        if ($reference === null) {
            $reference = $this->createReference($unit, $amount);
        }

        $intervalStarts = [];
        $start = $reference->addUnit($unit, $amount);
        while ($this->containsValue($start)) {
            $intervalStarts[] = $start;
            $start = $start->addUnit($unit, $amount);
        }

        return $this->splitBy($intervalStarts);
    }

    private function createReference(DateTimeUnit $unit, int $amount): DateTime
    {
        switch ($unit->getValue()) {
            case DateTimeUnit::YEAR:
                $year = $this->start->getYear();
                if ($amount > 1) {
                    $year = IntCalc::roundDownTo($year, $amount);
                }

                return DateTime::createFromComponents($year, 1, 1, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::QUARTER:
                if ($amount > 1) {
                    throw new NotImplementedException('Behavior of quarters for amount larger than 1 is not defined.');
                }
                $month = IntCalc::roundDownTo($this->start->getMonth() - 1, 3) + 1;

                return DateTime::createFromComponents($this->start->getYear(), $month, 1, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::MONTH:
                $month = $this->start->getMonth();
                if ($amount > 1) {
                    $month = IntCalc::roundDownTo($month - 1, $amount) + 1;
                }

                return DateTime::createFromComponents($this->start->getYear(), $month, 1, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::WEEK:
                if ($amount > 1) {
                    $year = (int) $this->start->format('o');
                    $week = (int) $this->start->format('W');
                    $week = IntCalc::roundDownTo($week - 1, $amount) + 1;

                    return Date::createFromIsoYearAndWeek($year, $week, 1)->toDateTime($this->start->getTimezone());
                } else {
                    $dayOfWeek = $this->start->getDayOfWeek();

                    return $this->start->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0, 0, 0);
                }
            case DateTimeUnit::DAY:
                $day = $this->start->getDay();
                if ($amount > 1) {
                    $day = IntCalc::roundDownTo($day - 1, $amount) + 1;
                }

                return DateTime::createFromComponents($this->start->getYear(), $this->start->getMonth(), $day, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::HOUR:
                $hours = null;
                if ($amount > 1) {
                    $hours = range(0, 23, $amount);
                }
                /** @var DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $hours);

                return $reference;
            case DateTimeUnit::MINUTE:
            case DateTimeUnit::SECOND:
                $units = null;
                if ($amount > 1) {
                    $units = range(0, 59, $amount);
                }
                /** @var DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $units);

                return $reference;
            case DateTimeUnit::MILISECOND:
                $miliseconds = null;
                if ($amount > 1) {
                    $miliseconds = range(0, 999, $amount);
                }
                /** @var DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $miliseconds);

                return $reference;
            case DateTimeUnit::MICROSECOND:
                $microseconds = null;
                if ($amount > 1) {
                    $microseconds = range(0, 999999, $amount);
                }
                /** @var DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $microseconds);

                return $reference;
            default:
                throw new ShouldNotHappenException('Unreachable');
        }
    }

    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = new DateTime(self::MAX);
        $end = new DateTime(self::MIN);
        foreach ($items as $item) {
            if ($item->start < $start) {
                $start = $item->start;
            }
            if ($item->end > $end) {
                $end = $item->end;
            }
        }

        return new static($start, $end);
    }

    public function intersect(self ...$items): self
    {
        $items[] = $this;
        /** @var self[] $items */
        $items = Arr::sortComparable($items);

        /** @var self $result */
        $result = array_shift($items);
        foreach ($items as $item) {
            if ($result->start < $item->start) {
                if ($result->end < $item->start) {
                    return self::empty();
                }
                $result = new static($item->start, $result->end);
            }
            if ($result->end > $item->end) {
                if ($result->start > $item->end) {
                    return self::empty();
                }
                $result = new static($result->start, $item->end);
            }
        }

        return $result;
    }

    public function union(self ...$items): DateTimeIntervalSet
    {
        $items[] = $this;
        /** @var self[] $items */
        $items = Arr::sortComparable($items);

        /** @var DateTimeInterval $current */
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

        return new DateTimeIntervalSet($results);
    }

    public function difference(self ...$items): DateTimeIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new DateTimeIntervalSet($results);
    }

    public function subtract(self ...$items): DateTimeIntervalSet
    {
        $results = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            foreach ($results as $r => $interval) {
                $startLower = $interval->start < $item->start;
                $endHigher = $interval->end > $item->end;
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($interval->start, $item->start);
                    $results[] = new static($item->end, $interval->end);
                } elseif ($startLower) {
                    if ($interval->end < $item->start) {
                        // r1****r2    i1----i2
                        continue;
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start);
                    }
                } elseif ($endHigher) {
                    if ($interval->start > $item->end) {
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

        return new DateTimeIntervalSet(array_values($results));
    }

    public function invert(): DateTimeIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param DateTimeInterval ...$items
     * @return array<array{0: DateTimeInterval, 1: int}> ($interval, $count)
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
     * @param DateTimeInterval ...$items
     * @return DateTimeInterval[]
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
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                } elseif ($a->end <= $b->start || $a->start >= $b->end) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($a->start->equals($b->start)) {
                    if ($a->end->isAfter($b->end)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2=b2
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($a->start < $b->start) {
                    if ($a->end->equals($b->end)) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end->isAfter($b->end)) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end->isAfter($b->end)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2----b2
                        // b1----a1----a2=b2
                        // b1----b2=a1---a2
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
