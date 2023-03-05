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

class InvalidDateTimeUnitException extends TimeException
{

    public function __construct(DateTimeUnit $unit, ?Throwable $previous = null)
    {
        $expected = $unit->isTime() ? 'Date' : 'Time';

        parent::__construct("$expected unit expected, but '{$unit->getValue()}' given.", $previous);
    }

}
