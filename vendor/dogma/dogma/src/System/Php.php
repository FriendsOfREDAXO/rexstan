<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Re;
use Dogma\StaticClassMixin;
use const INFO_GENERAL;
use const PHP_INT_SIZE;
use const PHP_SAPI;
use function extension_loaded;
use function ob_get_clean;
use function ob_start;
use function phpinfo;

class Php
{
    use StaticClassMixin;

    public static function is32bit(): bool
    {
        return PHP_INT_SIZE < 8;
    }

    public static function is64bit(): bool
    {
        return PHP_INT_SIZE === 8;
    }

    public static function getSapi(): Sapi
    {
        return Sapi::get(PHP_SAPI);
    }

    public static function isMultithreaded(): bool
    {
        return self::getSapi()->isMultithreaded() || self::hasPthreads() || self::isThreadSafe();
    }

    public static function hasPthreads(): bool
    {
        return extension_loaded('pthreads');
    }

    public static function isThreadSafe(): bool
    {
        static $threadSafe;
        if ($threadSafe === null) {
            ob_start();
            phpinfo(INFO_GENERAL);
            $info = (string) ob_get_clean();
            $threadSafe = (bool) Re::match($info, '~Thread Safety\s*</td>\s*<td[^>]*>\s*enabled~');
        }

        return $threadSafe;
    }

}
