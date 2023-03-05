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

class InvalidValueException extends Exception
{

    /** @var mixed */
    protected $value;

    /**
     * @param mixed $value
     * @param mixed $type
     */
    public function __construct($value, $type, ?Throwable $previous = null)
    {
        $valueFormatted = ExceptionValueFormatter::format($value);
        $type = ExceptionTypeFormatter::format($type);

        parent::__construct("Value $valueFormatted is not a valid value of $type.", $previous);

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}
