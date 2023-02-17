<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\StaticClassMixin;

/**
 * @deprecated will be removed. use Os instead
 */
class Environment
{
    use StaticClassMixin;

    public static function isWindows(): bool
    {
        return Os::isWindows();
    }

}
