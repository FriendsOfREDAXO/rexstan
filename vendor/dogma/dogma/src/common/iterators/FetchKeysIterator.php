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
use IteratorIterator;
use ReturnTypeWillChange;
use Traversable;
use function is_array;

/**
 * Returns only selected keys from iterated arrays (e.g. rows) as keys and values.
 *
 * @extends IteratorIterator<mixed, mixed, Traversable<mixed, mixed>>
 */
class FetchKeysIterator extends IteratorIterator
{
    use StrictBehaviorMixin;

    /** @var int|string|null */
    private $keysKey;

    /** @var int|string|null */
    private $valuesKey;

    /**
     * @param iterable<mixed> $iterable
     * @param int|string|null $keysKey
     * @param int|string|null $valuesKey
     */
    public function __construct(iterable $iterable, $keysKey = null, $valuesKey = null)
    {
        $iterable = IteratorHelper::iterableToIterator($iterable);

        parent::__construct($iterable);

        $this->keysKey = $keysKey;
        $this->valuesKey = $valuesKey;
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        if ($this->valuesKey === null) {
            return parent::current();
        } else {
            $value = parent::current();
            if (!is_array($value) && !$value instanceof ArrayAccess) {
                throw new InvalidTypeException('array or ArrayAccess', $value);
            }
            return $value[$this->valuesKey];
        }
    }

    /**
     * @return string|int
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        if ($this->keysKey === null) {
            return parent::key();
        } else {
            $value = parent::current();
            if (!is_array($value) && !$value instanceof ArrayAccess) {
                throw new InvalidTypeException('array or ArrayAccess', $value);
            }
            return $value[$this->keysKey];
        }
    }

}
