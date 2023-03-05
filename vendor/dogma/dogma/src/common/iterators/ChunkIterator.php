<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use IteratorIterator;
use Traversable;

/**
 * Returns results from given iterator in chunks.
 *
 * @extends IteratorIterator<mixed, mixed, Traversable<mixed, mixed>>
 */
class ChunkIterator extends IteratorIterator
{
    use StrictBehaviorMixin;

    /** @var int */
    private $chunkSize;

    /** @var int */
    private $key;

    /** @var mixed[] */
    private $chunk;

    /**
     * @param iterable|mixed[] $iterable
     */
    public function __construct(iterable $iterable, int $chunkSize)
    {
        Check::positive($chunkSize);
        $iterable = IteratorHelper::iterableToIterator($iterable);

        $this->chunkSize = $chunkSize;

        parent::__construct($iterable);
    }

    public function rewind(): void
    {
        parent::rewind();

        $this->next();
        $this->key = 0;
    }

    public function next(): void
    {
        $this->chunk = [];
        for ($i = 0; $i < $this->chunkSize && parent::valid(); $i++) {
            $this->chunk[] = parent::current();
            parent::next();
        }
        $this->key++;
    }

    /**
     * @return mixed[]
     */
    public function current(): array
    {
        return $this->chunk;
    }

    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return (bool) $this->chunk;
    }

}
