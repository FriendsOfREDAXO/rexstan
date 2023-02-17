<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Dumpable;
use Dogma\Equalable;
use IteratorAggregate;

/**
 * @template T
 * @extends IteratorAggregate<int, T>
 */
interface IntervalSet extends Equalable, Dumpable, IteratorAggregate
{

    /**
     * @return Interval[]
     */
    public function getIntervals(): array;

    public function isEmpty(): bool;

    //public function containsValue(T $value): bool;

    /**
     * @return mixed|Interval
     */
    public function envelope();//: Interval<T>;

    //public function normalize(): IntervalSet<T>;

    //public function add(IntervalSet<T> $set): IntervalSet<T>;

    //public function addIntervals(Interval<T> ...$intervals): IntervalSet<T>;

    //public function subtract(IntervalSet<T> $set): IntervalSet<T>;

    //public function subtractIntervals(Interval<T> ...$intervals): IntervalSet<T>;

    //public function intersect(IntervalSet<T> $set): IntervalSet<T>;

    //public function intersect(Interval<T> ...$intervals): IntervalSet<T>;

    //public function filterByLength(string $operator, int|float $length): IntervalSet<T>;

    /**
     * @return self|mixed
     */
    public function map(callable $mapper);

    /**
     * Map and filter
     * @return self|mixed
     */
    public function collect(callable $mapper);

}
