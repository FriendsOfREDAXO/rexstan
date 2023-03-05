<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Type;

/**
 * Interface for creating instances from raw data and vice versa
 */
interface TypeHandler
{

    /**
     * Used as a key in array returned by getParameters().
     * Indicates only one parameter is expected instead of array.
     */
    public const SINGLE_PARAMETER = '';

    /**
     * Returns true if handler accepts the type represented by $type parameter.
     *
     * @return bool
     */
    public function acceptsType(Type $type): bool;

    /**
     * Returns list of parameters where keys are the expected keys in parameters array received by createInstance()
     * and keys returned by exportInstance(). Values of array are the expected/returned types of parameters.
     *
     * When a single parameter is expected (not an array), constant SINGLE_PARAMETER, should be used as the key.
     *
     * When both key and type do not matter, value NULL may be returned. NULL is translated by MappingBuilder to:
     * [Handler::SINGLE_PARAMETER => Type(Type::MIXED)]
     *
     * Type::MIXED is the only type, that stops MappingBuilder from further unwrapping the type definition. Use this
     * type when you don't want the value to be changed at all.
     *
     * @return Type[]|null ($parameterName => $type)
     */
    public function getParameters(Type $type): ?array;

    /**
     * Expects array of parameters or a single parameter and returns the instantiated value of given type.
     *
     * When all parameters are be NULL, only a single NULL value is expected instead of array of NULLs.
     *
     * May use given Mapper for mapping some intermediate values.
     *
     * @param mixed|mixed[]|null $parameters
     * @return mixed
     */
    public function createInstance(Type $type, $parameters, Mapper $mapper);

    /**
     * Expects an instance and returns array of its original parameters or a single parameter.
     *
     * May use given Mapper for reverse mapping some intermediate values.
     *
     * @param mixed|null $instance
     * @return mixed|mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper);

}
