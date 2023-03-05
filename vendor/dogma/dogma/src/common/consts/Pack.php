<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Pack
{
    use StaticClassMixin;

    public const NUL_STRING = 'a'; // NUL-padded string
    public const SPACE_STRING = 'A'; // SPACE-padded string
    public const HEX_LE = 'h'; // Hex string, low nibble first
    public const HEX = 'H'; // Hex string, high nibble first
    public const INT8 = 'c'; // signed char
    public const UINT8 = 'C'; // unsigned char
    public const INT16 = 's'; // signed short (always 16 bit, machine byte order)
    public const UINT16 = 'S'; // unsigned short (always 16 bit, machine byte order)
    public const UINT16_BE = 'n'; // unsigned short (always 16 bit, big endian byte order)
    public const UINT16_LE = 'v'; // unsigned short (always 16 bit, little endian byte order)
    public const INT = 'i'; // signed integer (machine dependent size and byte order)
    public const UINT = 'I'; // unsigned integer (machine dependent size and byte order)
    public const INT32 = 'l'; // signed long (always 32 bit, machine byte order)
    public const UINT32 = 'L'; // unsigned long (always 32 bit, machine byte order)
    public const UINT32_BE = 'N'; // unsigned long (always 32 bit, big endian byte order)
    public const UINT32_LE = 'V'; // unsigned long (always 32 bit, little endian byte order)
    public const INT64 = 'q'; // signed long long (always 64 bit, machine byte order)
    public const UINT64 = 'Q'; // unsigned long long (always 64 bit, machine byte order)
    public const UINT64_BE = 'J'; // unsigned long long (always 64 bit, big endian byte order)
    public const UINT64_LE = 'P'; // unsigned long long (always 64 bit, little endian byte order)
    public const FLOAT = 'f'; // float (machine dependent size and representation)
    public const FLOAT_LE = 'g'; // float (machine dependent size, little endian byte order)
    public const FLOAT_BE = 'G'; // float (machine dependent size, big endian byte order)
    public const DOUBLE = 'd'; // double (machine dependent size and representation)
    public const DOUBLE_LE = 'e'; // double (machine dependent size, little endian byte order)
    public const DOUBLE_BE = 'E'; // double (machine dependent size, big endian byte order)
    public const NUL = 'x'; // NUL byte
    public const BACK = 'X'; // Back up one byte
    public const NUL_STRING_ALIAS = 'Z'; // NUL-padded string (new in PHP 5.5)
    public const NUL_FILL = '@'; // NUL-fill to absolute position

}
