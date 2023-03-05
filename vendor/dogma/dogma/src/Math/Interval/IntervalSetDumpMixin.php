<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Cls;
use Dogma\Obj;
use function count;
use function implode;
use function sprintf;

/**
 * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
 */
trait IntervalSetDumpMixin
{

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        $intervals = [];
        foreach ($this->intervals as $interval) {
            $intervals[] = $interval->dump();
        }

        return $intervals !== []
            ? sprintf(
                "%s(%d #%s) [\n    %s\n]",
                Cls::short(static::class),
                count($intervals),
                Obj::dumpHash($this),
                implode("\n    ", $intervals)
            )
            : sprintf(
                '%s(0 #%s)',
                Cls::short(static::class),
                Obj::dumpHash($this)
            );
    }

}
