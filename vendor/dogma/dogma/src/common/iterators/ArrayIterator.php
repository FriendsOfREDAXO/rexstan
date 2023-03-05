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
use function key;
use function next;
use function reset;

/**
 * Minimal interface replacement of bloated native ArrayIterator
 *
 * @implements Iterator<mixed, mixed>
 */
class ArrayIterator implements Iterator
{
    use StrictBehaviorMixin;

    /** @var mixed[] */
    protected $array;

    /**
     * @param mixed[] $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function rewind(): void
    {
        reset($this->array);
    }

    public function next(): void
    {
        next($this->array);
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
