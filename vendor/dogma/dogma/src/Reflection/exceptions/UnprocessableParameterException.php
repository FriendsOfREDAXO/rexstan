<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Reflection;

use Dogma\Exception;
use ReflectionMethod;
use Throwable;

class UnprocessableParameterException extends Exception implements ReflectionException
{

    public function __construct(ReflectionMethod $method, string $message, ?Throwable $previous = null)
    {
        $class = $method->getDeclaringClass()->getName();

        parent::__construct("Unprocessable parameter on $class::{$method->getName()}: $message", $previous);
    }

}
