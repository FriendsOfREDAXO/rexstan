<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Enum\StringEnum;
use const PHP_OS;

/**
 * PHP_OS_FAMILY values (PHP 7.2+)
 */
class Os extends StringEnum
{

    public const WINDOWS = 'Windows';
    public const BSD = 'BSD';
    public const DARWIN = 'Darwin';
    public const SOLARIS = 'Solaris';
    public const LINUX = 'Linux';
    public const UNKNOWN = 'Unknown';

    private const FAMILIES = [
        'CYGWIN_NT-5.1' => self::WINDOWS,
        'Darwin' => self::DARWIN,
        'FreeBSD' => self::BSD,
        'HP-UX' => self::UNKNOWN,
        'IRIX64' => self::UNKNOWN,
        'Linux' => self::LINUX,
        'NetBSD' => self::BSD,
        'OpenBSD' => self::BSD,
        'SunOS' => self::SOLARIS,
        'Unix' => self::UNKNOWN,
        'WIN32' => self::WINDOWS,
        'WINNT' => self::WINDOWS,
        'Windows' => self::WINDOWS,
    ];

    public static function family(): string
    {
        return self::FAMILIES[PHP_OS] ?? self::UNKNOWN;
    }

    public static function isWindows(): bool
    {
        return self::family() === self::WINDOWS;
    }

}
