<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use DateTimeInterface;
use Dogma\Time\Date;
use Dogma\Time\Time;

interface DateTimeFormatter
{

    /**
     * @param DateTimeInterface|Date|Time $dateTime
     * @return string
     */
    public function format($dateTime, ?string $format = null): string;

}
