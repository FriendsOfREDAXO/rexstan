<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Type;
use ReflectionClass;

/**
 * Creates instance via keyword 'new' by filling constructor parameters
 */
abstract class ConstructorHandler implements TypeHandler
{

    /** @var MethodTypeParser */
    private $parser;

    public function __construct(MethodTypeParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return Type[]
     */
    public function getParameters(Type $type): array
    {
        /** @var class-string $class */
        $class = $type->getName();
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();
        if ($constructor === null) {
            throw new NoConstructorException($class);
        }

        return $this->parser->getParameterTypes($constructor);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     * @param mixed[] $parameters
     * @return object
     */
    public function createInstance(Type $type, $parameters, Mapper $mapper)
    {
        $orderedParams = [];
        foreach ($this->getParameters($type) as $name => $paramType) {
            // it is up to class constructor to check the types!
            $orderedParams[] = $parameters[$name];
        }

        return $type->getInstance(...$orderedParams);
    }

}
