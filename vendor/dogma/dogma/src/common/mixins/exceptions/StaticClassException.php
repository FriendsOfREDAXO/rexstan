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

class StaticClassException extends Exception
{

    public function __construct(string $class, ?Throwable $previous = null)
    {
        parent::__construct("Cannot instantiate a static class $class.", $previous);
    }

}
