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

class InvalidWeekDateIntervalException extends InvalidIntervalException
{

    public function __construct(DateOrTime $start, DateOrTime $end, ?Throwable $previous = null)
    {
        parent::__construct(
            "Interval start and end should be aligned to start and end of a week (monday and sunday). Start date {$start->format()} and end date {$end->format()} are not.",
            $previous
        );
    }

}
