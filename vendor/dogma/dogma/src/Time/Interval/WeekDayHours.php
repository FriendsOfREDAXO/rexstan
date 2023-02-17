<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\Cls;
use Dogma\Dumpable;
use Dogma\Obj;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DayOfWeek;
use function sprintf;

class WeekDayHours implements Pokeable, Dumpable
{
    use StrictBehaviorMixin;

    /** @var DayOfWeek */
    private $day;

    /** @var TimeIntervalSet */
    private $hours;

    final public function __construct(DayOfWeek $day, TimeIntervalSet $hours)
    {
        $this->day = $day;
        $this->hours = $hours;
    }

    public static function createFromOpeningTime(DayOfWeek $day, TimeInterval $opening, ?TimeInterval $break = null): self
    {
        return new static($day, $break !== null ? $opening->subtract($break) : new TimeIntervalSet([$opening]));
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        $this->hours->poke();
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%s %s #%s)',
            Cls::short(static::class),
            $this->day->dump(),
            $this->hours->dump(),
            Obj::dumpHash($this)
        );
    }

    public function getDay(): DayOfWeek
    {
        return $this->day;
    }

    public function getTimeIntervalSet(): TimeIntervalSet
    {
        return $this->hours;
    }

    /**
     * @return TimeInterval[]
     */
    public function getTimeIntervals(): array
    {
        return $this->hours->getIntervals();
    }

}
