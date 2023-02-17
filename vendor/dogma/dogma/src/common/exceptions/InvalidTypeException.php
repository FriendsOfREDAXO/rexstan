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

class InvalidTypeException extends Exception
{

    /**
     * @param string|string[] $expectedType
     * @param mixed $actualType
     */
    public function __construct($expectedType, $actualType, ?Throwable $previous = null)
    {
        $expectedType = ExceptionTypeFormatter::format($expectedType);
        $actualType = ExceptionTypeFormatter::format($actualType);

        parent::__construct("Expected a value of type $expectedType. $actualType given.", $previous);
    }

}
