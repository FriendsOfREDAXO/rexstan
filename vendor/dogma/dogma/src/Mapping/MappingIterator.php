<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\ArrayIterator;
use Dogma\IteratorHelper;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use Iterator;
use IteratorAggregate;
use function is_array;

/**
 * @implements Iterator<mixed, mixed>
 */
class MappingIterator implements Iterator
{
    use StrictBehaviorMixin;

    /** @var Iterator<mixed, mixed> */
    private $source;

    /** @var Type */
    private $type;

    /** @var Mapper */
    private $mapper;

    /** @var bool */
    private $reverse;

    /** @var int */
    private $key = 0;

    /**
     * @param iterable|mixed[] $source
     */
    public function __construct(iterable $source, Type $type, Mapper $mapper, bool $reverse = false)
    {
        if (is_array($source)) {
            $this->source = new ArrayIterator($source);
        } elseif ($source instanceof IteratorAggregate) {
            $this->source = IteratorHelper::iterableToIterator($source);
        } elseif ($source instanceof Iterator) {
            $this->source = $source;
        }

        $this->type = $type;
        $this->mapper = $mapper;
        $this->reverse = $reverse;
    }

    public function rewind(): void
    {
        $this->source->rewind();
        $this->key = 0;
    }

    public function next(): void
    {
        $this->key++;
        $this->source->next();
    }

    public function valid(): bool
    {
        return $this->source->valid();
    }

    /**
     * @return mixed|null
     */
    public function current()
    {
        if ($this->reverse) {
            return $this->mapper->reverseMap($this->type, $this->source->current());
        } else {
            return $this->mapper->map($this->type, $this->source->current());
        }
    }

    public function key(): int
    {
        return $this->key;
    }

}
