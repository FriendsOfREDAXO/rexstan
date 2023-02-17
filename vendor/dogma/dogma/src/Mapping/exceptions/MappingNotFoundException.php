<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\Exception;
use Dogma\Type;
use Throwable;

class MappingNotFoundException extends Exception implements MappingException
{

    /** @var Type */
    private $type;

    public function __construct(Type $size, ?Throwable $previous = null)
    {
        parent::__construct("Mapping for type {$size->getId()} was not found.", $previous);

        $this->type = $size;
    }

    public function getType(): Type
    {
        return $this->type;
    }

}
