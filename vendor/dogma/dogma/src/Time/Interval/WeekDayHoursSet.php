<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\ArrayIterator;
use Dogma\Cls;
use Dogma\Dumpable;
use Dogma\Obj;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use Dogma\Time\DayOfWeek;
use Dogma\Time\DaysOfWeek;
use Dogma\Time\InvalidWeekDayHoursSetException;
use IteratorAggregate;
use Traversable;
use function count;
use function implode;
use function ksort;
use function sprintf;

/**
 * @implements IteratorAggregate<WeekDayHours>
 */
class WeekDayHoursSet implements Pokeable, Dumpable, IteratorAggregate
{
    use StrictBehaviorMixin;

    /** @var WeekDayHours[] */
    private $weekDayHours = [];

    /**
     * @param WeekDayHours[] $weekDayHoursList
     */
    final public function __construct(array $weekDayHoursList)
    {
        foreach ($weekDayHoursList as $weekDayHours) {
            $day = $weekDayHours->getDay()->getValue();
            if (isset($this->weekDayHours[$day])) {
                throw new InvalidWeekDayHoursSetException($weekDayHours->getDay());
            }
            $this->weekDayHours[$day] = $weekDayHours;
        }
        ksort($this->weekDayHours);
    }

    public function createFromDaysOfWeekAndOpeningTime(
        DaysOfWeek $days,
        TimeInterval $opening,
        ?TimeInterval $break = null
    ): self
    {
        $dayItems = [];
        foreach (DayOfWeek::getInstances() as $day) {
            if ($days->containsDay($day)) {
                $dayItems[] = WeekDayHours::createFromOpeningTime($day, $opening, $break);
            }
        }

        return new static($dayItems);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        foreach ($this->weekDayHours as $hours) {
            $hours->poke();
        }
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        $hours = [];
        foreach ($this->weekDayHours as $interval) {
            $hours[] = $interval->dump();
        }

        return $hours !== []
            ? sprintf(
                "%s(%d #%s) [\n    %s\n]",
                Cls::short(static::class),
                count($hours),
                Obj::dumpHash($this),
                implode("\n    ", $hours)
            )
            : sprintf(
                '%s(0 #%s)',
                Cls::short(static::class),
                Obj::dumpHash($this)
            );
    }

    public function containsValue(DateTime $dateTime): bool
    {
        $date = $dateTime->getDate();

        return DateTimeIntervalSet::createFromDateIntervalAndWeekDayHoursSet(new DateInterval($date, $date), $this)->containsValue($dateTime);
    }

    public function containsInterval(DateTimeInterval $interval): bool
    {
        return DateTimeIntervalSet::createFromDateIntervalAndWeekDayHoursSet($interval->toDateInterval(), $this)->containsInterval($interval);
    }

    /**
     * @return WeekDayHours[]
     */
    public function getWeekDayHours(): array
    {
        return $this->weekDayHours;
    }

    /**
     * @return Traversable<WeekDayHours>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->weekDayHours);
    }

    public function getByDay(DayOfWeek $dayOfWeek): ?WeekDayHours
    {
        $day = $dayOfWeek->getValue();

        return $this->getByDayNumber($day);
    }

    public function getByDayNumber(int $dayNumber): ?WeekDayHours
    {
        return $this->weekDayHours[$dayNumber] ?? null;
    }

}
