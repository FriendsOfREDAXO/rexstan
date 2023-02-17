<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Throwable;

class InvalidOrDeprecatedTimeZoneException extends TimeException
{

    public function __construct(string $name, ?Throwable $previous = null)
    {
        static $url = 'https://secure.php.net/manual/en/timezones.others.php';

        parent::__construct(
            "Time zone name '$name' is not valid or is deprecated. See $url for deprecated time zones info.",
            $previous
        );
    }

}
