<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Comparable;
use Dogma\Dumpable;
use Dogma\Equalable;
use Dogma\IntersectComparable;

interface Interval /*<T>*/ extends Equalable, Comparable, IntersectComparable, Dumpable
{

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(): string;

    /**
     * @return mixed
     */
    public function getStart(); //: T

    /**
     * @return mixed
     */
    public function getEnd(); //: T

    /**
     * @return mixed[]
     */
    public function getStartEnd(): array; //: array<T>

    public function isEmpty(): bool;

    //public function equals(self $interval): bool;

    //public function containsValue(T $value): bool;

    //public function contains(self $interval): bool;

    //public function intersects(self $interval): bool;

    //public function touches(self $interval): bool;

    // actions ---------------------------------------------------------------------------------------------------------

    /**
     * @return mixed|IntervalSet
     */
    public function split(int $parts);//: IntervalSet<T>;

    /**
     * @param mixed[] $intervalStarts |array<T>
     * @return mixed|IntervalSet
     */
    public function splitBy(array $intervalStarts);//: IntervalSet<T>;

    // A1****A2****B1****B2 -> [A1, B2]
    //public function envelope(self ...$items): self;

    // A and B
    // A1----B1****A2----B2 -> [B1, A2]
    // A1----A2    B1----B2 -> [empty]
    //public function intersect(self ...$items): self;

    // A or B
    // A1****B1****A2****B2 -> {[A1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    //public function union(self ...$items): IntervalSet<T>;

    // A xor B
    // A1****B1----A2****B2 -> {[A1, A2], [B1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    //public function difference(self ...$items): IntervalSet<T>;

    // A minus B
    // A1****B1----A2----B2 -> {[A1, B1]}
    // A1****A2    B1----B2 -> {[A1, A2]}
    //public function subtract(self ...$items): IntervalSet<T>;

    /**
     * @return mixed|IntervalSet
     */
    public function invert();//: IntervalSet<T>;

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param Interval ...$items
     * @return Interval[][]|int[][] ($ident => ($interval, $count))
     */
    //public static function countOverlaps(self ...$items): array;

    /**
     * O(n log n)
     * @param Interval ...$items
     * @return Interval[]
     */
    //public static function explodeOverlaps(self ...$items): array;

    /**
     * @param self[] $intervals
     * @return self[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $intervals): array;

    /**
     * @param self[] $intervals
     * @return self[]
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sortByStart(array $intervals): array;

}
