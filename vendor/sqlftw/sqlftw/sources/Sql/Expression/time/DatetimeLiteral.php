<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\InvalidDefinitionException;
use function checkdate;
use function preg_match;
use function round;
use function str_pad;
use function strlen;
use function substr;
use const PREG_UNMATCHED_AS_NULL;
use const STR_PAD_LEFT;

/**
 * e.g. datetime '2020-01-01 12:00:'
 */
class DatetimeLiteral implements TimeValue
{

    private string $value;

    private string $normalized;

    public function __construct(string $value)
    {
        // !#$%&*+-,./:;<=>^|\~
        if (preg_match('~^\s*(\d{2,4})\D+(\d\d)\D+(\d\d)[T \r\n\t\f.-]+(\d\d?)(?:\D+(\d\d?)(?:\D+(\d\d?)(?:\.(\d*))?(?:([+-])(\d\d):(\d\d))?)?)?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [YY]YY-MM-DD hh[:mm[:ss[.μs][±hh:mm]]]
            [, $year, $month, $day, $hours, $minutes, $seconds, $fraction, $offsetSign, $offsetHours, $offsetMinutes] = $m;
        } elseif (preg_match('~^\s*(\d{2,4})(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(?:\.(\d*))?(?:([+-])(\d\d):(\d\d))?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [YY]YYMMDDhhmmss[.μs][±hh:mm]
            [, $year, $month, $day, $hours, $minutes, $seconds, $fraction, $offsetSign, $offsetHours, $offsetMinutes] = $m;
        } else {
            throw new InvalidDefinitionException("Invalid datetime literal format: '$value'.");
        }

        $this->normalized = self::checkAndNormalize((string) $year, (string) $month, (string) $day, (string) $hours, $minutes, $seconds, $fraction, $offsetSign, $offsetHours, $offsetMinutes);
        $this->value = $value;
    }

    public static function checkAndNormalize(
        string $year,
        string $month,
        string $day,
        string $hours,
        ?string $minutes,
        ?string $seconds,
        ?string $fraction,
        ?string $offsetSign,
        ?string $offsetHours,
        ?string $offsetMinutes
    ): string
    {
        if ($minutes === null) {
            $minutes = '00';
        }
        if ($seconds === null) {
            $seconds = '00';
        }
        if ($hours > 23 || $minutes > 59 || $seconds > 59) {
            throw new InvalidDefinitionException("Invalid time value.");
        }
        $microseconds = (int) round((float) ('0.' . $fraction) * 1000000);
        if ($microseconds === 1000000) {
            throw new InvalidDefinitionException("Invalid time value.");
        }

        // normalize 2-digit year
        if (strlen($year) === 2) {
            if ($year === '00') {
                $year = '0000';
            } elseif ($year < 70) {
                $year = (string) ((int) $year + 2000);
            } elseif ($year < 100) {
                $year = (string) ((int) $year + 1900);
            }
        }

        // zero in date
        if ((int) $month !== 0 && (int) $day !== 0 && !checkdate((int) $month, (int) $day, (int) $year)) {
            throw new InvalidDefinitionException("Invalid date value.");
        }

        if ($offsetSign === '-' && $offsetHours === '00' && $offsetMinutes === '00') {
            throw new InvalidDefinitionException("Invalid timezone offset value.");
        } elseif ($offsetHours > 14 || ($offsetHours === '14' && $offsetMinutes !== '00')) {
            throw new InvalidDefinitionException("Timezone offset out of allowed range.");
        }

        return str_pad($year, 4, '0', STR_PAD_LEFT)
            . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)
            . '-' . str_pad($day, 2, '0', STR_PAD_LEFT)
            . ' ' . str_pad($hours, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT)
            . ($microseconds !== 0 ? '.' . $microseconds : '')
            . $offsetSign . $offsetHours . ($offsetHours !== null ? ':' : '') . $offsetMinutes;
    }

    public function hasZeroDate(): bool
    {
        return substr($this->value, 0, 10) === '0000-00-00';
    }

    public function hasZeroInDate(): bool
    {
        return substr($this->value, 5, 2) === '00' || substr($this->value, 8, 2) === '00';
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getNormalizedValue(): string
    {
        return $this->normalized;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DATETIME ' . $formatter->formatString($this->value);
    }

}
