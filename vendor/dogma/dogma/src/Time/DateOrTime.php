<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\Time\Format\DateTimeValues;

interface DateOrTime extends Equalable, Comparable
{

    public function format(string $format = ''): string;

    public function fillValues(DateTimeValues $values): void;

}
