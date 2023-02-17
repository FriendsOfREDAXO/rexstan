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
use function str_pad;
use function strlen;
use function substr;
use const STR_PAD_LEFT;

/**
 * e.g. date '2020-01-01'
 */
class DateLiteral implements TimeValue
{

    private string $value;

    private string $normalized;

    public function __construct(string $value)
    {
        if (preg_match('~^\s*(\d{2,4})\D+(\d\d?)\D+(\d\d?)\s*$~', $value, $m) === 1) {
            // [YY]YY-MM-DD
            [, $year, $month, $day] = $m;
        } elseif (preg_match('~^\s*(\d{2,4})(\d\d)(\d\d)\s*$~', $value, $m) === 1) {
            // [YY]YYMMDD
            [, $year, $month, $day] = $m;
        } else {
            throw new InvalidDefinitionException("Invalid date literal format: '$value'.");
        }

        $this->normalized = self::checkAndNormalize($year, $month, $day);
        $this->value = $value;
    }

    public static function checkAndNormalize(string $year, string $month, string $day): string
    {
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
        if ($month > 12 || $day > 31) {
            throw new InvalidDefinitionException("Invalid date value.");
        }

        // zero in date
        if ((int) $month !== 0 && (int) $day !== 0 && !checkdate((int) $month, (int) $day, (int) $year)) {
            throw new InvalidDefinitionException("Invalid date value.");
        }

        return str_pad($year, 4, '0', STR_PAD_LEFT)
            . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)
            . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
    }

    public function hasZeroDate(): bool
    {
        return $this->value === '0000-00-00';
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
        return 'DATE ' . $formatter->formatString($this->value);
    }

}
