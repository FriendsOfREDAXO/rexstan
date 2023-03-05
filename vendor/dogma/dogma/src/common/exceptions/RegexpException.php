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
use const PREG_BACKTRACK_LIMIT_ERROR;
use const PREG_BAD_UTF8_ERROR;
use const PREG_BAD_UTF8_OFFSET_ERROR;
use const PREG_INTERNAL_ERROR;
use const PREG_JIT_STACKLIMIT_ERROR;
use const PREG_RECURSION_LIMIT_ERROR;

class RegexpException extends Exception
{

    public const MESSAGES = [
        0 => 'Unknown error',
        PREG_INTERNAL_ERROR => 'Internal error',
        PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exhausted',
        PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exhausted',
        PREG_BAD_UTF8_ERROR => 'Invalid UTF-8 string',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Offset did not correspond to a beginning of a valid UTF-8 code point',
        PREG_JIT_STACKLIMIT_ERROR => 'Failed due to limited JIT stack space',
    ];

    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        $message = self::MESSAGES[$code];

        parent::__construct($message, $previous, $code);
    }

}
