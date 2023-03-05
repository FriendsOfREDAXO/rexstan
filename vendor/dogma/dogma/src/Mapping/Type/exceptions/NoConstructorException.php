<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Exception;
use Throwable;

class NoConstructorException extends Exception implements MappingTypeException
{

    /** @var class-string */
    private $class;

    /**
     * @param class-string $class
     */
    public function __construct(string $class, ?Throwable $previous = null)
    {
        parent::__construct("Class $class cannot be mapped using ConstructorHandler, as it has no constructor.", $previous);

        $this->class = $class;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

}
