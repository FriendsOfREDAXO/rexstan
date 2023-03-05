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

class InvalidTimeSpanException extends TimeException
{

    public function __construct(string $intervalString, ?Throwable $previous = null)
    {
        parent::__construct("Invalid date/time interval: $intervalString", $previous);
    }

}
