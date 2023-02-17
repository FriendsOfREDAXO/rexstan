<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Throwable;

class InvalidWeekDayHoursSetException extends TimeException
{

    public function __construct(DayOfWeek $dayOfWeek, ?Throwable $previous = null)
    {
        parent::__construct("Each day of week can be specified only once in WeekDayHoursSet. {$dayOfWeek->getName()} was specified twice.", $previous);
    }

}
