<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Throwable;

class ValueOutOfBoundsException extends ValueOutOfRangeException
{

    /**
     * @param int|float|string $value
     * @param string|Type $type
     */
    public function __construct($value, $type, ?Throwable $previous = null)
    {
        $value = ExceptionValueFormatter::format($value);
        $type = ExceptionTypeFormatter::format($type);

        Exception::__construct("Value $value cannot fit to data type $type.", $previous);
    }

}
