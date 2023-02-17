<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;
use function count;

/**
 * Immutable list of fixed number of parameters
 *
 * @implements ArrayAccess<int, mixed>
 * @implements IteratorAggregate<int, mixed>
 */
class Tuple implements Countable, IteratorAggregate, ArrayAccess
{
    use StrictBehaviorMixin;
    use ImmutableArrayAccessMixin;

    /** @var mixed[] */
    private $items;

    /**
     * @param mixed ...$items
     */
    public function __construct(...$items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

}
