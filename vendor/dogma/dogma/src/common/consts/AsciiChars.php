<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable Squiz.Arrays.ArrayDeclaration.ValueNoNewline
// phpcs:disable Squiz.Arrays.ArrayDeclaration.KeySpecified

namespace Dogma;

class AsciiChars
{
    use StaticClassMixin;

    public const SPECIAL_CHARS = [
        Char::NUL, Char::SOH, Char::STX, Char::ETX, Char::EOT, Char::ENQ, Char::ACK, Char::BEL,
        Char::BS,  Char::HT,  Char::LF,  Char::VT,  Char::FF,  Char::CR,  Char::SO,  Char::SI,
        Char::DLE, Char::DC1, Char::DC2, Char::DC3, Char::DC4, Char::NAK, Char::SYN, Char::ETB,
        Char::CAN, Char::EM,  Char::SUB, Char::ESC, Char::FS,  Char::GS,  Char::RS,  Char::US,
        127 => Char::DEL,
    ];

    public const SPECIAL_WITHOUT_WHITESPACE = [
        Char::NUL, Char::SOH, Char::STX, Char::ETX, Char::EOT, Char::ENQ, Char::ACK, Char::BEL,
        Char::BS,  /*  HT & LF  */ 11 => Char::VT,  Char::FF,/*CR*/ 14 => Char::SO,  Char::SI,
        Char::DLE, Char::DC1, Char::DC2, Char::DC3, Char::DC4, Char::NAK, Char::SYN, Char::ETB,
        Char::CAN, Char::EM,  Char::SUB, Char::ESC, Char::FS,  Char::GS,  Char::RS,  Char::US,
        127 => Char::DEL,
    ];

    public const WHITESPACE = [9 => "\t", 10 => "\n", 13 => "\r", 32 => ' '];

    public const UPPER_LETTERS = [65 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    public const LOWER_LETTERS = [97 => 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    public const LETTERS = self::UPPER_LETTERS + self::LOWER_LETTERS;

    public const NUMBERS = [48 => '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    public const ALPHANUMERIC = self::LETTERS + self::NUMBERS;

    public const OPERATORS = [42 => '*', 43 => '+', 45 => '-', 47 => '/', 60 => '<', 61 => '=', 62 => '>', 94 => '^', 126 => '~'];
    public const PUNCTUATION = [33 => '!', 44 => ',', 46 => '.', 58 => ':', 59 => ';', 63 => '?'];
    public const BRACKETS = [40 => '(', 41 => ')', 91 => '[', 93 => ']', 123 => '{', 125 => '}'];
    public const QUOTES = [34 => '"', 39 => "'", 96 => '`'];
    public const OTHER = [35 => '#', 36 => '$', 37 => '%', 38 => '&', 64 => '@', 92 => '\\', 95 => '_', 124 => '|'];
    public const SYMBOLS = self::OPERATORS + self::PUNCTUATION + self::BRACKETS + self::QUOTES + self::OTHER;

    public const PRINTABLE = self::ALPHANUMERIC + self::SYMBOLS + self::WHITESPACE;
    public const ALL = self::ALPHANUMERIC + self::SYMBOLS + self::WHITESPACE + self::SPECIAL_WITHOUT_WHITESPACE;

    // RFC 4648
    public const BASE_64 = self::NUMBERS + self::LETTERS + [43 => '+', 47 => '/'];
    public const BASE_64_URL = self::NUMBERS + self::LETTERS + [45 => '-', 95 => '_'];
    public const BASE_32 = self::UPPER_LETTERS + [50 => 2, 3, 4, 5, 6, 7];
    public const BASE_32_HEX = self::NUMBERS + [65 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V'];
    public const BASE_16 = self::NUMBERS + [65 => 'A', 'B', 'C', 'D', 'E', 'F'];

}
