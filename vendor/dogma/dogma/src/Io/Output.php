<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\StaticClassMixin;
use function ob_get_clean;
use function ob_start;

class Output
{
    use StaticClassMixin;

    public static function capture(callable $callback): string
    {
        ob_start();
        $callback();
        /** @var string $results */
        $results = ob_get_clean();

        return $results;
    }

}
