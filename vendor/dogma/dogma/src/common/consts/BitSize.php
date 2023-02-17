<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use const PHP_INT_SIZE;
use function in_array;

class BitSize
{
    use StaticClassMixin;

    public const BITS_8 = 8;
    public const BITS_16 = 16;
    public const BITS_24 = 24;
    public const BITS_32 = 32;
    public const BITS_48 = 48;
    public const BITS_64 = 64;

    public const DEFAULT_INT_SIZE = PHP_INT_SIZE * 8;
    public const DEFAULT_FLOAT_SIZE = self::BITS_64;

    /**
     * @return int[]
     */
    public static function getIntSizes(): array
    {
        return [
            self::BITS_8,
            self::BITS_16,
            self::BITS_24,
            self::BITS_32,
            self::BITS_48,
            self::BITS_64,
        ];
    }

    /**
     * @return int[]
     */
    public static function getFloatSizes(): array
    {
        return [
            self::BITS_32,
            self::BITS_64,
        ];
    }

    /**
     * @throws InvalidSizeException
     */
    public static function checkIntSize(int $size): void
    {
        $sizes = self::getIntSizes();
        if (!in_array($size, $sizes, true)) {
            throw new InvalidSizeException(Type::INT, $size, $sizes);
        }
    }

    /**
     * @throws InvalidSizeException
     */
    public static function checkFloatSize(int $size): void
    {
        $sizes = self::getFloatSizes();
        if (!in_array($size, $sizes, true)) {
            throw new InvalidSizeException(Type::FLOAT, $size, $sizes);
        }
    }

}
