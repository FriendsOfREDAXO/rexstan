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
use function sprintf;

/**
 * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
 */
trait IntervalDumpMixin
{

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%s - %s #%s)',
            Cls::short(static::class),
            $this->start->dump(),
            $this->end->dump(),
            Obj::dumpHash($this)
        );
    }

}
