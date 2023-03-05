<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

interface DateTimeOrTime extends DateOrTime
{

    public function getHours(): int;

    public function getMinutes(): int;

    public function getSeconds(): int;

    public function getMiliseconds(): int;

    public function getMicroseconds(): int;

}
