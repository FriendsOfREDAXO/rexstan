<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Iterator;
use ReturnTypeWillChange;
use function array_values;
use function count;

/**
 * Cycles through given iterators. Iterators should return same number of items.
 *
 * @implements Iterator<mixed, mixed>
 */
class RoundRobinIterator implements Iterator
{
    use StrictBehaviorMixin;

    /** @var Iterator[] */
    private $iterators;

    /** @var int */
    private $current;

    /** @var int */
    private $key;

    /** @var bool */
    private $allowUneven = false;

    /**
     * @param iterable|mixed[] ...$iterables
     */
    final public function __construct(iterable ...$iterables)
    {
        $this->iterators = [];
        foreach ($iterables as $iterable) {
            $this->iterators[] = IteratorHelper::iterableToIterator($iterable);
        }
        $this->current = 0;
        $this->key = 0;
    }

    /**
     * @param iterable|mixed[] ...$iterables
     * @return static
     */
    public static function uneven(iterable ...$iterables): self
    {
        $self = new static(...$iterables);
        $self->allowUneven = true;

        return $self;
    }

    public function rewind(): void
    {
        foreach ($this->iterators as $iterator) {
            $iterator->rewind();
        }
        $this->current = 0;
        $this->key = 0;
    }

    public function next(): void
    {
        $this->current++;
        $this->key++;
        if ($this->current >= count($this->iterators)) {
            $this->current = 0;
        }
        if ($this->current === 0) {
            foreach ($this->iterators as $iterator) {
                $iterator->next();
            }
        }
    }

    public function valid(): bool
    {
        if ($this->current === 0) {
            $valid = 0;
            $invalid = 0;
            foreach ($this->iterators as $i => $iterator) {
                if ($iterator->valid()) {
                    $valid++;
                } else {
                    $invalid++;
                    unset($this->iterators[$i]);
                }
            }
            if ($valid === 0) {
                return false;
            } elseif ($invalid === 0) {
                return true;
            } elseif ($this->allowUneven) {
                $this->iterators = array_values($this->iterators);
                return true;
            } else {
                throw new UnevenIteratorSourcesException('Given iterators do not return same amount of items.');
            }
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->iterators[$this->current]->current();
    }

    /**
     * @return mixed|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

}
