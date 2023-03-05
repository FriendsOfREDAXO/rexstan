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

class UndefinedMethodException extends Exception
{

    public function __construct(string $class, string $method, ?Throwable $previous = null)
    {
        parent::__construct("Method $class::$method() is not defined or is not accessible", $previous);
    }

}
