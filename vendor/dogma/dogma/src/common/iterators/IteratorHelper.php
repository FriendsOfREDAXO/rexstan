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
use IteratorAggregate;
use function is_array;

class IteratorHelper
{
    use StaticClassMixin;

    /**
     * @param iterable|mixed[] $iterable
     * @return Iterator<mixed, mixed>
     */
    public static function iterableToIterator(iterable $iterable): Iterator
    {
        if (is_array($iterable)) {
            return new ArrayIterator($iterable);
        } elseif ($iterable instanceof Iterator) {
            return $iterable;
        }

        while ($iterable instanceof IteratorAggregate) {
            $iterable = $iterable->getIterator();
        }

        /** @var Iterator<mixed, mixed> $iterable */
        $iterable = $iterable;

        return $iterable;
    }

}
