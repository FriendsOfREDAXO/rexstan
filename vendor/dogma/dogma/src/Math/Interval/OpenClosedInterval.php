<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Classes.ClassMemberSpacing

namespace Dogma\Math\Interval;

interface OpenClosedInterval /*<T>*/ extends Interval /*<T>*/
{

    public const OPEN = true;
    public const CLOSED = false;

    public const SPLIT_OPEN_STARTS = 1;
    public const SPLIT_CLOSED = 0;
    public const SPLIT_OPEN_ENDS = -1;

    //public static function closed(T $start, T $end): self<T>;

    //public static function open(T $start, T $end): self<T>;

    //public static function openStart(T $start, T $end): self<T>;

    //public static function openEnd(T $start, T $end): self<T>;

    public function hasOpenStart(): bool;

    public function hasOpenEnd(): bool;

}
