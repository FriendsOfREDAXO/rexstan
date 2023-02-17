<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Web;

use Dogma\Exception;
use Dogma\InvalidValueException;
use Throwable;

class InvalidUrlException extends InvalidValueException
{

    public function __construct(string $value, ?Throwable $previous = null)
    {
        Exception::__construct("Invalid URL format: '$value'", $previous);
    }

}
