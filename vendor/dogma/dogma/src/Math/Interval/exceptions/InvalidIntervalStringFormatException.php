<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Exception;
use Throwable;

class InvalidIntervalStringFormatException extends Exception
{

    public function __construct(string $string, ?Throwable $previous = null)
    {
        parent::__construct("Cannot parse interval borders from string '$string'.", $previous);
    }

}
