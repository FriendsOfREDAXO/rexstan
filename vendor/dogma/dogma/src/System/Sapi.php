<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Arr;
use Dogma\Enum\StringEnum;

class Sapi extends StringEnum
{

    public const AOL_SERVER = 'aolserver';
    public const APACHE = 'apache';
    public const APACHE_2_FILTER = 'apache2filter';
    public const APACHE_2_HANDLER = 'apache2handler';
    public const CAUDIUM = 'caudium';
    public const CGI = 'cgi'; // (until PHP 5.3)
    public const CGI_FCGI = 'cgi-fcgi';
    public const CLI = 'cli';
    public const CLI_SERVER = 'cli-server';
    public const CONTINUITY = 'continuity';
    public const EMBED = 'embed';
    public const FPM_FCGI = 'fpm-fcgi';
    public const ISAPI = 'isapi';
    public const LITESPEED = 'litespeed';
    public const MILTER = 'milter';
    public const NSAPI = 'nsapi';
    public const PHTTPD = 'phttpd';
    public const PI3WEB = 'pi3web';
    public const ROXEN = 'roxen';
    public const THTTPD = 'thttpd';
    public const TUX = 'tux';
    public const WEBJAMES = 'webjames';

    /** @var string[] */
    private static $multithreaded = [
        self::AOL_SERVER,
        self::APACHE,
        self::APACHE_2_FILTER,
        self::APACHE_2_HANDLER,
        self::CAUDIUM,
        self::CONTINUITY,
        self::ISAPI,
        self::LITESPEED,
        self::MILTER,
        self::NSAPI,
        self::PHTTPD,
        self::PI3WEB,
        self::ROXEN,
        self::THTTPD,
        self::TUX,
        self::WEBJAMES,
    ];

    public function isCli(): bool
    {
        return $this->getValue() === self::CLI;
    }

    public function isMultithreaded(): bool
    {
        return Arr::contains(self::$multithreaded, $this->getValue());
    }

}
