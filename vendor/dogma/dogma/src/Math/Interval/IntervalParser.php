<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\StaticClassMixin;
use function count;
use function explode;
use function strlen;
use function trim;

class IntervalParser
{
    use StaticClassMixin;

    public const CLOSED_START = '[';
    public const CLOSED_END = ']';
    public const OPEN_START = '(';
    public const OPEN_END = ')';

    public const SEPARATORS = [',', '|', '/', ' - ', '--'];

    /**
     * @return array{0: string, 1: string, 2: bool|null, 3: bool|null} (string $start, string $end, ?bool $openStart, ?bool $openEnd)
     */
    public static function parseString(string $string): array
    {
        $openStart = $string[0] === self::OPEN_START
            ? true
            : ($string[0] === self::CLOSED_START ? false : null);
        $openEnd = $string[strlen($string) - 1] === self::OPEN_END
            ? true
            : ($string[strlen($string) - 1] === self::CLOSED_END ? false : null);

        $trimmed = trim($string, '[]()');
        foreach (self::SEPARATORS as $separator) {
            $parts = explode($separator, $trimmed);
            if (count($parts) === 2) {
                return [trim($parts[0]), trim($parts[1]), $openStart, $openEnd];
            }
        }

        throw new InvalidIntervalStringFormatException($string);
    }

}
