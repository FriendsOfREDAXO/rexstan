<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use Traversable;

class Mapper
{
    use StrictBehaviorMixin;

    /** @var MappingContainer */
    private $mappings;

    public function __construct(MappingContainer $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @param mixed[] $data
     * @return mixed
     */
    public function map(Type $type, array $data)
    {
        return $this->mappings->getMapping($type)->mapForward($data, $this);
    }

    /**
     * @param mixed $data
     * @return mixed[]
     */
    public function reverseMap(Type $type, $data): array
    {
        return $this->mappings->getMapping($type)->mapBack($data, $this);
    }

    /**
     * @param iterable|mixed[] $data
     * @return Traversable|mixed[]
     */
    public function mapMany(Type $type, iterable $data): Traversable
    {
        /** @var Type $itemType */
        $itemType = $type->getItemType();
        $iterator = new MappingIterator($data, $itemType, $this);

        /** @var Traversable<mixed> $result */
        $result = $type->getInstance($iterator);

        return $result;
    }

    /**
     * @param iterable|mixed[] $data
     * @return MappingIterator
     */
    public function reverseMapMany(Type $type, iterable $data): MappingIterator
    {
        /** @var Type $itemType */
        $itemType = $type->getItemType();

        return new MappingIterator($data, $itemType, $this, true);
    }

}
