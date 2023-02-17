<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Check;
use Dogma\InvalidTypeException;
use Dogma\Mapping\Mapper;
use Dogma\Type;

class ScalarsHandler implements TypeHandler
{

    public function acceptsType(Type $type): bool
    {
        return $type->isScalar();
    }

    /**
     * @return Type[]|null
     */
    public function getParameters(Type $type): ?array
    {
        return null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        switch (true) {
            case $type->isBool():
                Check::bool($value);
                return $value;
            case $type->isInt():
                Check::int($value);
                if ($type->getSize() !== null) {
                    Check::bounds($value, $type);
                }
                return $value;
            case $type->isFloat():
                Check::float($value);
                if ($type->getSize() !== null) {
                    Check::bounds($value, $type);
                }
                return $value;
            case $type->isString():
                Check::string($value);
                return $value;
            case $type->isNumeric():
                Check::float($value);
                $int = (int) $value;
                if ((float) $int === $value) {
                    return $int;
                }
                return $value;
            default:
                throw new InvalidTypeException(Type::SCALAR, $value);
        }
    }

    /**
     * @param mixed $instance
     * @return mixed
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper)
    {
        if ($type->getSize() !== null) {
            Check::bounds($instance, $type);
        }
        return $instance;
    }

}
