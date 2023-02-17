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
use function preg_match;
use function substr;
use const PREG_UNMATCHED_AS_NULL;

/**
 * e.g. timestamp '2020-01-01 12:00:'
 */
class TimestampLiteral implements TimeValue
{

    private string $value;

    private string $normalized;

    public function __construct(string $value)
    {
        if (preg_match('~^\s*(\d{2,4})\D+(\d\d)\D+(\d\d)[T \r\n\t\f.-]+(\d\d?)(?:\D+(\d\d?)(?:\D+(\d\d?)(?:\.(\d*))?(?:([+-])(\d\d):(\d\d))?)?)?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [YY]YY-MM-DD hh:mm:ss[.μs][±hh:mm]
            [, $year, $month, $day, $hours, $minutes, $seconds, $fraction, $offsetSign, $offsetHours, $offsetMinutes] = $m;
        } elseif (preg_match('~^\s*(\d{2,4})(\d\d)(\d\d)[T \r\n\t\f.-]*(\d\d)(\d\d)(\d\d)(?:\.(\d*))?(?:([+-])(\d\d):(\d\d))?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [YY]YYMMDDhhmmss[.μs][±hh:mm]
            [, $year, $month, $day, $hours, $minutes, $seconds, $fraction, $offsetSign, $offsetHours, $offsetMinutes] = $m;
        } else {
            throw new InvalidDefinitionException("Invalid timestamp literal format: '$value'.");
        }

        $this->normalized = DatetimeLiteral::checkAndNormalize((string) $year, (string) $month, (string) $day, (string) $hours, $minutes, $seconds, $fraction, $offsetSign, $offsetHours, $offsetMinutes);
        $this->value = $value;
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
        return 'TIMESTAMP ' . $formatter->formatString($this->value);
    }

}
