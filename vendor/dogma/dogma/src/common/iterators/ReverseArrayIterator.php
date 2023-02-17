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
use function current;
use function end;
use function key;
use function prev;

/**
 * For iterating and array in reverse order without making a reversed copy of the array.
 *
 * @implements Iterator<mixed, mixed>
 */
class ReverseArrayIterator implements Iterator
{
    use StrictBehaviorMixin;

    /** @var mixed[] */
    private $array;

    /**
     * @param mixed[] $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function rewind(): void
    {
        end($this->array);
    }

    public function next(): void
    {
        prev($this->array);
    }

    public function valid(): bool
    {
        return key($this->array) !== null;
    }

    /**
     * @return int|string|null
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->array);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->array);
    }

}
