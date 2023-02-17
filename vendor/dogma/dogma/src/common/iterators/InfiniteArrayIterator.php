<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function count;

/**
 * Repeats given array infinitely, including keys.
 */
class InfiniteArrayIterator extends ArrayIterator
{

    public function next(): void
    {
        parent::next();

        if (!$this->valid() && count($this->array) > 0) {
            $this->rewind();
        }
    }

}
