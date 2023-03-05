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
use Dogma\Type;
use Throwable;

class NoHandlerForTypeException extends Exception implements MappingTypeException
{

    /** @var Type */
    private $type;

    public function __construct(Type $size, ?Throwable $previous = null)
    {
        parent::__construct("No type handler for type {$size->getId()} is registered.", $previous);

        $this->type = $size;
    }

    public function getType(): Type
    {
        return $this->type;
    }

}
