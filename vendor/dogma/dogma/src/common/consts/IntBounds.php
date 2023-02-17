<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

class IntBounds
{
    use StaticClassMixin;

    public const UINT8_MAX = 255;
    public const UINT16_MAX = 65535;
    public const UINT24_MAX = 16777215;
    public const UINT32_MAX = 4294967295;
    public const UINT48_MAX = 281474976710655;
    public const UINT64_MAX = PHP_INT_MAX; // this is actually 63 bits, since PHP int is always signed

    public const INT8_MIN = -128;
    public const INT8_MAX = 127;
    public const INT16_MIN = -32768;
    public const INT16_MAX = 32767;
    public const INT24_MIN = -8388608;
    public const INT24_MAX = 8388607;
    public const INT32_MIN = -2147483648;
    public const INT32_MAX = 2147483647;
    public const INT48_MIN = -140737488355328;
    public const INT48_MAX = 140737488355327;
    public const INT64_MIN = PHP_INT_MIN;
    public const INT64_MAX = PHP_INT_MAX;

    /**
     * @return int[]
     */
    public static function getRange(int $size, string $sign = Sign::SIGNED): array
    {
        $bounds = [
            Sign::UNSIGNED => [
                BitSize::BITS_8 => [0, self::UINT8_MAX],
                BitSize::BITS_16 => [0, self::UINT16_MAX],
                BitSize::BITS_24 => [0, self::UINT24_MAX],
                BitSize::BITS_32 => [0, self::UINT32_MAX],
                BitSize::BITS_48 => [0, self::UINT48_MAX],
                BitSize::BITS_64 => [0, self::UINT64_MAX],
            ],
            Sign::SIGNED => [
                BitSize::BITS_8 => [self::INT8_MIN, self::INT8_MAX],
                BitSize::BITS_16 => [self::INT16_MIN, self::INT16_MAX],
                BitSize::BITS_24 => [self::INT24_MIN, self::INT24_MAX],
                BitSize::BITS_32 => [self::INT32_MIN, self::INT32_MAX],
                BitSize::BITS_48 => [self::INT48_MIN, self::INT48_MAX],
                BitSize::BITS_64 => [self::INT64_MIN, self::INT64_MAX],
            ],
        ];

        return $bounds[$sign][$size];
    }

}
