<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type\Enum;

use Dogma\Enum\IntEnum;
use Dogma\Enum\StringEnum;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\Type\TypeHandler;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;

/**
 * Creates an enum from raw value and vice versa
 */
class EnumHandler implements TypeHandler
{
    use StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(IntEnum::class) || $type->isImplementing(StringEnum::class);
    }

    /**
     * @return string[]|null
     */
    public function getParameters(Type $type): ?array
    {
        return null;
    }

    /**
     * @param int|string $value
     * @return IntEnum|StringEnum
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        /** @var callable $cb */
        $cb = [$type->getName(), 'get'];

        return $cb($value);
    }

    /**
     * @param IntEnum|StringEnum $enum
     * @return int|string
     */
    public function exportInstance(Type $type, $enum, Mapper $mapper)
    {
        return $enum->getValue();
    }

}
