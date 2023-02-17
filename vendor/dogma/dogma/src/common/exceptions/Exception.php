<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Exception as PhpException;
use Throwable;

class Exception extends PhpException
{
    use StrictBehaviorMixin;

    public function __construct(string $message, ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }

}
