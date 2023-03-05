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
use ReturnTypeWillChange;
use Traversable;
use function call_user_func;

/**
 * Calls given callback on each value or key and returns result.
 *
 * @extends IteratorIterator<mixed, mixed, Traversable<mixed, mixed>>
 */
class CallbackIterator extends IteratorIterator
{
    use StrictBehaviorMixin;

    /** @var callable */
    private $valuesCallback;

    /** @var callable|null */
    private $keysCallback;

    /**
     * @param iterable|mixed[] $iterable
     */
    public function __construct(iterable $iterable, callable $valuesCallback, ?callable $keysCallback = null)
    {
        $this->valuesCallback = $valuesCallback;
        $this->keysCallback = $keysCallback;

        $iterable = IteratorHelper::iterableToIterator($iterable);

        parent::__construct($iterable);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        $key = parent::key();

        if ($this->keysCallback === null) {
            return $key;
        }

        return call_user_func($this->keysCallback, $key);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        $value = parent::current();

        return call_user_func($this->valuesCallback, $value);
    }

}
