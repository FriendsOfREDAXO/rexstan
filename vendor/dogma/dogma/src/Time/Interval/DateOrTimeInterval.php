<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\Time\DateOrTime;
use Dogma\Time\Span\DateTimeSpan;

interface DateOrTimeInterval
{

    /**
     * @return DateOrTime|mixed
     */
    public function getStart(); // : DateOrTime

    /**
     * @return DateOrTime|mixed
     */
    public function getEnd(); // : DateOrTime

    public function getSpan(): DateTimeSpan;

    // todo: not implemented in DateInterval and TimeInterval
    //public function splitByUnit(DateTimeUnit $unit): DateOrTimeIntervalSet

}
