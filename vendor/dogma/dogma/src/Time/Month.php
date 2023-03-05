<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: jan feb apr jul aug sep oct nov dec

namespace Dogma\Time;

use Dogma\Enum\IntEnum;
use Dogma\InvalidValueException;
use function array_search;
use function is_int;
use function strtolower;

class Month extends IntEnum
{

    public const JANUARY = 1;
    public const FEBRUARY = 2;
    public const MARCH = 3;
    public const APRIL = 4;
    public const MAY = 5;
    public const JUNE = 6;
    public const JULY = 7;
    public const AUGUST = 8;
    public const SEPTEMBER = 9;
    public const OCTOBER = 10;
    public const NOVEMBER = 11;
    public const DECEMBER = 12;

    public static function january(): self
    {
        return self::get(self::JANUARY);
    }

    public static function february(): self
    {
        return self::get(self::FEBRUARY);
    }

    public static function march(): self
    {
        return self::get(self::MARCH);
    }

    public static function april(): self
    {
        return self::get(self::APRIL);
    }

    public static function may(): self
    {
        return self::get(self::MAY);
    }

    public static function june(): self
    {
        return self::get(self::JUNE);
    }

    public static function july(): self
    {
        return self::get(self::JULY);
    }

    public static function august(): self
    {
        return self::get(self::AUGUST);
    }

    public static function september(): self
    {
        return self::get(self::SEPTEMBER);
    }

    public static function october(): self
    {
        return self::get(self::OCTOBER);
    }

    public static function november(): self
    {
        return self::get(self::NOVEMBER);
    }

    public static function december(): self
    {
        return self::get(self::DECEMBER);
    }

    public static function getByName(string $name): self
    {
        $name = strtolower($name);
        $number = array_search($name, self::getNames(), true);
        if (!is_int($number)) {
            throw new InvalidValueException($name, static::class);
        }

        return self::get($number);
    }

    /**
     * @return string[]
     */
    public static function getNames(): array
    {
        return [
            self::JANUARY => 'january',
            self::FEBRUARY => 'february',
            self::MARCH => 'march',
            self::APRIL => 'april',
            self::MAY => 'may',
            self::JUNE => 'june',
            self::JULY => 'july',
            self::AUGUST => 'august',
            self::SEPTEMBER => 'september',
            self::OCTOBER => 'october',
            self::NOVEMBER => 'november',
            self::DECEMBER => 'december',
        ];
    }

    /**
     * @return string[]
     */
    public static function getShortcuts(): array
    {
        return [
            self::JANUARY => 'jan',
            self::FEBRUARY => 'feb',
            self::MARCH => 'mar',
            self::APRIL => 'apr',
            self::MAY => 'may',
            self::JUNE => 'jun',
            self::JULY => 'jul',
            self::AUGUST => 'aug',
            self::SEPTEMBER => 'sep',
            self::OCTOBER => 'oct',
            self::NOVEMBER => 'nov',
            self::DECEMBER => 'dec',
        ];
    }

    /**
     * @return int[]
     */
    public static function getLengths(bool $leapYear = false): array
    {
        return $leapYear
            ? [
                self::JANUARY => 31,
                self::FEBRUARY => 29,
                self::MARCH => 31,
                self::APRIL => 30,
                self::MAY => 31,
                self::JUNE => 30,
                self::JULY => 31,
                self::AUGUST => 31,
                self::SEPTEMBER => 30,
                self::OCTOBER => 31,
                self::NOVEMBER => 30,
                self::DECEMBER => 31,
            ] : [
                self::JANUARY => 31,
                self::FEBRUARY => 28,
                self::MARCH => 31,
                self::APRIL => 30,
                self::MAY => 31,
                self::JUNE => 30,
                self::JULY => 31,
                self::AUGUST => 31,
                self::SEPTEMBER => 30,
                self::OCTOBER => 31,
                self::NOVEMBER => 30,
                self::DECEMBER => 31,
            ];
    }

    public function getName(): string
    {
        return self::getNames()[$this->getValue()];
    }

    public function getShortcut(): string
    {
        return self::getShortcuts()[$this->getName()];
    }

    public function getDays(bool $leapYear): int
    {
        return self::getLengths($leapYear)[$this->getValue()];
    }

}
