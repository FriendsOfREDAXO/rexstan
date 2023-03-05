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

class InvalidEncodingException extends InvalidValueException
{

    public function __construct(string $value, string $expectedEncoding, ?Throwable $previous = null)
    {
        $formatted = ExceptionValueFormatter::format($value);

        Exception::__construct("Value $formatted is not a valid $expectedEncoding string.", $previous);
    }

}
