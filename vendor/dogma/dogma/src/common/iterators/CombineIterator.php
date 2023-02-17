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

/**
 * Combines values from two given iterators as keys and values. Iterators should return same number of items.
 *
 * @implements Iterator<mixed, mixed>
 */
class CombineIterator implements Iterator
{
    use StrictBehaviorMixin;

    /** @var Iterator<mixed, mixed> */
    private $keys;

    /** @var Iterator<mixed, mixed> */
    private $values;

    /**
     * @param iterable|mixed[] $keys
     * @param iterable|mixed[] $values
     */
    public function __construct(iterable $keys, iterable $values)
    {
        $this->keys = IteratorHelper::iterableToIterator($keys);
        $this->values = IteratorHelper::iterableToIterator($values);
    }

    public function rewind(): void
    {
        $this->keys->rewind();
        $this->values->rewind();
    }

    public function next(): void
    {
        $this->keys->next();
        $this->values->next();
    }

    public function valid(): bool
    {
        $keysValid = $this->keys->valid();
        $valuesValid = $this->values->valid();
        if ($keysValid xor $valuesValid) {
            throw new UnevenIteratorSourcesException(
                $keysValid ? 'Values iterator runned out of values.' : 'Keys iterator runned out of values.'
            );
        }

        return $keysValid && $valuesValid;
    }

    /**
     * @return mixed|null
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->values->valid() ? $this->values->current() : null;
    }

    /**
     * @return int|string|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->keys->valid() ? $this->keys->current() : null;
    }

}
