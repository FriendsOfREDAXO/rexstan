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
use Dogma\StrictBehaviorMixin;
use Dogma\Tuple;
use Dogma\Type;

class TupleHandler implements TypeHandler
{
    use StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->is(Tuple::class);
    }

    /**
     * @return Type[]
     */
    public function getParameters(Type $type): array
    {
        /** @var Type[] $types */
        $types = $type->getItemType();

        return $types;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param mixed[] $items
     * @return Tuple
     */
    public function createInstance(Type $type, $items, Mapper $mapper): Tuple
    {
        return new Tuple(...$items);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param Tuple $instance
     * @return mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): array
    {
        return $instance->toArray();
    }

}
