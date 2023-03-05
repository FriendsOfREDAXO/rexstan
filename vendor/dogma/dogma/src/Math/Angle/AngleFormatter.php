<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Angle;

use Dogma\StrictBehaviorMixin;
use function abs;
use function floor;
use function in_array;
use function number_format;
use function rtrim;
use function str_split;

class AngleFormatter
{
    use StrictBehaviorMixin;

    public const DEGREES = 'D';
    public const DEGREES_FLOORED = 'd';
    public const MINUTES = 'M';
    public const MINUTES_FLOORED = 'm';
    public const SECONDS = 'S';
    public const SECONDS_FLOORED = 's';

    public const FORMAT_DEFAULT = 'd:m:S';
    public const FORMAT_PRETTY = 'd˚m′S″';
    public const FORMAT_NUMBER = 'D';

    /** @var string[] */
    private static $specialCharacters = [
        self::DEGREES,
        self::DEGREES_FLOORED,
        self::MINUTES,
        self::MINUTES_FLOORED,
        self::SECONDS,
        self::SECONDS_FLOORED,
    ];

    /** @var string */
    private $format;

    /** @var int */
    private $maxDecimals;

    /** @var string */
    private $decimalPoint;

    public function __construct(string $format = self::FORMAT_DEFAULT, int $maxDecimals = 6, string $decimalPoint = '.')
    {
        $this->format = $format;
        $this->maxDecimals = $maxDecimals;
        $this->decimalPoint = $decimalPoint;
    }

    /**
     * Ch.  Description
     * ---- -------------------------
     * Escaping:
     * %    Escape character. Use %% for printing "%"
     *
     * Skip groups:
     * [    Group start, skip if zero
     * ]    Group end, skip if zero
     *
     * Objects:
     * D    Degrees with fraction
     * d    Degrees floored
     * M    Minutes with fraction
     * m    Minutes floored
     * S    Seconds with fraction
     * s    Seconds floored
     *
     * @return string
     */
    public function format(float $degrees, ?string $format = null, ?int $maxDecimals = null, ?string $decimalPoint = null): string
    {
        $format = $format ?? $this->format;
        $maxDecimals = $maxDecimals ?? $this->maxDecimals;
        $decimalPoint = $decimalPoint ?? $this->decimalPoint;

        $sign = $degrees < 0;
        $degrees = abs($degrees);

        $result = '';
        $escaped = false;
        foreach (str_split($format) as $character) {
            if ($character === '%' && !$escaped) {
                $escaped = true;
            } elseif ($escaped === false && in_array($character, self::$specialCharacters, true)) {
                switch ($character) {
                    case self::DEGREES:
                        $result .= $this->formatNumber($degrees, $maxDecimals, $decimalPoint);
                        break;
                    case self::DEGREES_FLOORED:
                        $result .= floor($degrees);
                        break;
                    case self::MINUTES:
                        $minutes = ($degrees - floor($degrees)) * 60;
                        $result .= $this->formatNumber($minutes, $maxDecimals, $decimalPoint);
                        break;
                    case self::MINUTES_FLOORED:
                        $minutes = ($degrees - floor($degrees)) * 60;
                        $result .= floor($minutes);
                        break;
                    case self::SECONDS:
                        $minutes = ($degrees - floor($degrees)) * 60;
                        $seconds = ($minutes - floor($minutes)) * 60;
                        $result .= $this->formatNumber($seconds, $maxDecimals, $decimalPoint);
                        break;
                    case self::SECONDS_FLOORED:
                        $minutes = ($degrees - floor($degrees)) * 60;
                        $seconds = ($minutes - floor($minutes)) * 60;
                        $result .= floor($seconds);
                        break;
                }
                $escaped = false;
            } else {
                $result .= $character;
            }
        }

        return ($sign ? '-' : '') . $result;
    }

    private function formatNumber(float $number, int $maxDecimals, string $decimalPoint): string
    {
        $number = number_format($number, $maxDecimals, $decimalPoint, '');

        return rtrim(rtrim($number, '0'), $decimalPoint);
    }

}
