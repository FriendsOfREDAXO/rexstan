<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application;

use Dogma\Re;
use Dogma\StaticClassMixin;
use Dogma\Str;
use const STR_PAD_RIGHT;
use function str_pad;
use function strlen;

final class Colors
{
    use StaticClassMixin;

    /** @var bool */
    public static $off = false;

    public const WHITE = 'white';
    public const LGRAY = 'lgray';
    public const GRAY = 'gray';
    public const BLACK = 'black';
    public const RED = 'red';
    public const LRED = 'lred';
    public const GREEN = 'green';
    public const LGREEN = 'lgreen';
    public const BLUE = 'blue';
    public const LBLUE = 'lblue';
    public const CYAN = 'cyan';
    public const LCYAN = 'lcyan';
    public const PURPLE = 'purple';
    public const LPURPLE = 'lpurple';
    public const YELLOW = 'yellow';
    public const LYELLOW = 'lyellow';

    /** @var string[] */
    private static $fg = [
        self::WHITE => '1;37',
        self::LGRAY => '0;37',
        self::GRAY => '1;30',
        self::BLACK => '0;30',

        self::RED => '0;31',
        self::LRED => '1;31',
        self::GREEN => '0;32',
        self::LGREEN => '1;32',
        self::BLUE => '0;34',
        self::LBLUE => '1;34',

        self::CYAN => '0;36',
        self::LCYAN => '1;36',
        self::PURPLE => '0;35',
        self::LPURPLE => '1;35',
        self::YELLOW => '1;33',
        self::LYELLOW => '0;33',
    ];

    /** @var string[] */
    private static $bg = [
        self::LGRAY => '47',
        self::BLACK => '40',

        self::RED => '41',
        self::GREEN => '42',
        self::BLUE => '44',

        self::YELLOW => '43',
        self::PURPLE => '45',
        self::CYAN => '46',
    ];

    public static function color(string $string, ?string $foreground = null, ?string $background = null): string
    {
        if (self::$off) {
            return $string;
        }

        if (!isset(self::$fg[$foreground]) && !isset(self::$bg[$background])) {
            return $string;
        }

        $out = '';
        if (isset(self::$fg[$foreground])) {
            $out .= "\x1B[" . self::$fg[$foreground] . 'm';
        }
        if (isset(self::$bg[$background])) {
            $out .= "\x1B[" . self::$bg[$background] . 'm';
        }

        return $out . $string . "\x1B[0m";
    }

    public static function background(string $string, string $background): string
    {
        return self::color($string, null, $background);
    }

    /**
     * Remove formatting characters from a string
     * @return string
     */
    public static function remove(string $string): string
    {
        return Re::replace($string, '/\\x1B\\[[^m]+m/U', '');
    }

    /**
     * Safely pads string with formatting characters to length
     * @return string
     */
    public static function padString(string $string, int $length, string $with = ' ', int $type = STR_PAD_RIGHT): string
    {
        $original = self::remove($string);

        return str_pad($string, $length + strlen($string) - strlen($original), $with, $type);
    }

    public static function length(string $string): int
    {
        return Str::length(self::remove($string));
    }

    // shortcuts -------------------------------------------------------------------------------------------------------

    public static function white(string $string, ?string $background = null): string
    {
        return self::color($string, self::WHITE, $background);
    }

    public static function lgray(string $string, ?string $background = null): string
    {
        return self::color($string, self::LGRAY, $background);
    }

    public static function gray(string $string, ?string $background = null): string
    {
        return self::color($string, self::GRAY, $background);
    }

    public static function black(string $string, ?string $background = null): string
    {
        return self::color($string, self::BLACK, $background);
    }

    public static function red(string $string, ?string $background = null): string
    {
        return self::color($string, self::RED, $background);
    }

    public static function lred(string $string, ?string $background = null): string
    {
        return self::color($string, self::LRED, $background);
    }

    public static function green(string $string, ?string $background = null): string
    {
        return self::color($string, self::GREEN, $background);
    }

    public static function lgreen(string $string, ?string $background = null): string
    {
        return self::color($string, self::LGREEN, $background);
    }

    public static function blue(string $string, ?string $background = null): string
    {
        return self::color($string, self::BLUE, $background);
    }

    public static function lblue(string $string, ?string $background = null): string
    {
        return self::color($string, self::LBLUE, $background);
    }

    public static function cyan(string $string, ?string $background = null): string
    {
        return self::color($string, self::CYAN, $background);
    }

    public static function lcyan(string $string, ?string $background = null): string
    {
        return self::color($string, self::LCYAN, $background);
    }

    public static function purple(string $string, ?string $background = null): string
    {
        return self::color($string, self::PURPLE, $background);
    }

    public static function lpurple(string $string, ?string $background = null): string
    {
        return self::color($string, self::LPURPLE, $background);
    }

    public static function yellow(string $string, ?string $background = null): string
    {
        return self::color($string, self::YELLOW, $background);
    }

    public static function lyellow(string $string, ?string $background = null): string
    {
        return self::color($string, self::LYELLOW, $background);
    }

}
