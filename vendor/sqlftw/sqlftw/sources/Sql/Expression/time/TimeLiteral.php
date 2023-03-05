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
use function round;
use function str_pad;
use const PREG_UNMATCHED_AS_NULL;
use const STR_PAD_LEFT;

/**
 * e.g. time '12:00:00'
 */
class TimeLiteral implements TimeValue
{

    private string $value;

    private string $normalized;

    public function __construct(string $value)
    {
        if (preg_match('~^\s*(-)?(\d\d?) +(\d{1,4})(?:[:.](\d\d?)(?:[:.](\d\d?)(?:\.(\d*))?)?)?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [-]D hh[:mm[:ss[.μs]]]
            [, $sign, $days, $hours, $minutes, $seconds, $fraction] = $m;
        } elseif (preg_match('~^\s*(-)?(\d{1,4})[:.](\d\d?)(?:[:.](\d\d?)(?:\.(\d*))?)?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [-]hh:mm[:ss[.μs]]
            [, $sign, $hours, $minutes, $seconds, $fraction] = $m;
            $days = null;
        } elseif (preg_match('~^\s*(-)?(?:(\d{1,4})?(\d\d))?(\d\d)(?:\.(\d*))?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [-][[hh]mm]ss[.μs]
            [, $sign, $hours, $minutes, $seconds, $fraction] = $m;
            $days = null;
        } elseif (preg_match('~^\s*(-)?(\d)(?:\.(\d*))?\s*$~', $value, $m, PREG_UNMATCHED_AS_NULL) === 1) {
            // [-]s[.μs]
            [, $sign, $seconds, $fraction] = $m;
            $days = $hours = $minutes = null;
        } else {
            throw new InvalidDefinitionException("Invalid time literal format: '$value'");
        }

        $this->normalized = self::checkAndNormalize($sign, $days, $hours, $minutes, $seconds, $fraction);

        $this->value = $value;
    }

    public static function checkAndNormalize(?string $sign, ?string $days, ?string $hours, ?string $minutes, ?string $seconds, ?string $fraction): string
    {
        if ($hours === null) {
            $hours = '00';
        }
        if ($minutes === null) {
            $minutes = '00';
        }
        if ($seconds === null) {
            $seconds = '00';
        }
        if (($days !== null && ((int) $days * 24 + (int) $hours) > 838) || ($days === null && $hours > 838) || $minutes > 59 || $seconds > 59) {
            throw new InvalidDefinitionException("Invalid time value.");
        }
        $microseconds = (int) round((float) ('0.' . $fraction) * 1000000);
        if ($microseconds === 1000000) {
            throw new InvalidDefinitionException("Invalid time value.");
        }

        return $sign
            . ($days !== null ? ((int) $days) . ' ' : '')
            . str_pad($hours, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT)
            . ($microseconds !== 0 ? '.' . $microseconds : '');
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
        return 'TIME ' . $formatter->formatString($this->value);
    }

}
