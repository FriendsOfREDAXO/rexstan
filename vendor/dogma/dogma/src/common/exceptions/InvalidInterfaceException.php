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
use function get_class;
use function gettype;
use function interface_exists;
use function is_object;

class InvalidInterfaceException extends InvalidTypeException
{

    /**
     * @param mixed $value
     */
    public function __construct(string $expectedInterface, $value, ?Throwable $previous = null)
    {
        if (is_object($value)) {
            $type = get_class($value);
        } else {
            $type = gettype($value);
        }
        $class = true;
        if (interface_exists($expectedInterface)) {
            $class = false;
        }
        if ($class) {
            Exception::__construct("Expected an instance of $expectedInterface. $type given.", $previous);
        } else {
            Exception::__construct("Expected an object implementing $expectedInterface. $type given.", $previous);
        }
    }

}
