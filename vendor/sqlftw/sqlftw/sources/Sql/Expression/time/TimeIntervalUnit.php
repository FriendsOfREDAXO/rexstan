<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;
use function in_array;

/**
 * e.g. SECOND, DAY...
 */
class TimeIntervalUnit extends SqlEnum
{

    public const MICROSECOND = Keyword::MICROSECOND;
    public const SECOND = Keyword::SECOND;
    public const MINUTE = Keyword::MINUTE;
    public const HOUR = Keyword::HOUR;
    public const DAY = Keyword::DAY;
    public const WEEK = Keyword::WEEK;
    public const MONTH = Keyword::MONTH;
    public const QUARTER = Keyword::QUARTER;
    public const YEAR = Keyword::YEAR;

    public const SECOND_MICROSECOND = Keyword::SECOND_MICROSECOND; // 0.0
    public const MINUTE_SECOND = Keyword::MINUTE_SECOND; // 0:0
    public const HOUR_MINUTE = Keyword::HOUR_MINUTE; // 0:0
    public const DAY_HOUR = Keyword::DAY_HOUR; // 0 0
    public const YEAR_MONTH = Keyword::YEAR_MONTH; // 0-0

    public const MINUTE_MICROSECOND = Keyword::MINUTE_MICROSECOND; // 0:0.0
    public const HOUR_SECOND = Keyword::HOUR_SECOND; // 0:0:0
    public const DAY_MINUTE = Keyword::DAY_MINUTE; // 0 0:0

    public const HOUR_MICROSECOND = Keyword::HOUR_MICROSECOND; // 0:0:0.0
    public const DAY_SECOND = Keyword::DAY_SECOND; // 0 0:0:0

    public const DAY_MICROSECOND = Keyword::DAY_MICROSECOND; // 0 0:0:0.0

    /** @var array<self::*, int> */
    private static array $parts = [
        self::SECOND_MICROSECOND => 2,
        self::MINUTE_SECOND => 2,
        self::HOUR_MINUTE => 2,
        self::DAY_HOUR => 2,
        self::YEAR_MONTH => 2,
        self::MINUTE_MICROSECOND => 3,
        self::HOUR_SECOND => 3,
        self::DAY_MINUTE => 3,
        self::HOUR_MICROSECOND => 4,
        self::DAY_SECOND => 4,
        self::DAY_MICROSECOND => 5,
    ];

    /** @var array<self::*, string> */
    private static array $formats = [
        self::SECOND_MICROSECOND => '%d.%d',
        self::MINUTE_SECOND => '%d:%d',
        self::HOUR_MINUTE => '%d:%d',
        self::DAY_HOUR => '%d %d',
        self::YEAR_MONTH => '%d-%d',
        self::MINUTE_MICROSECOND => '%d:%d.%d',
        self::HOUR_SECOND => '%d:%d:%d',
        self::DAY_MINUTE => '%d %d:%d',
        self::HOUR_MICROSECOND => '%d:%d:%d.%d',
        self::DAY_SECOND => '%d %d:%d:%d',
        self::DAY_MICROSECOND => '%d %d:%d:%d.%d',
    ];

    /** @var array<string, string> */
    private static array $patterns = [
        self::SECOND_MICROSECOND => '~^\\d+(?:\\.\\d+)$~',
        self::MINUTE_SECOND => '~^\\d+(?::\\d+)$~',
        self::HOUR_MINUTE => '~^\\d+(?::\\d+)$~',
        self::DAY_HOUR => '~^\\d+(?: \\d+)$~',
        self::YEAR_MONTH => '~^\\d+(?:-\\d+)$~',
        self::MINUTE_MICROSECOND => '~^\\d+(?::\\d+(?:\\.\\d+))$~',
        self::HOUR_SECOND => '~^\\d+(?::\\d+(?::\\d+))$~',
        self::DAY_MINUTE => '~^\\d+(?: \\d+(?::\\d+))$~',
        self::HOUR_MICROSECOND => '~^\\d+(?::\\d+(?::\\d+(?:\\.\\d+)))$~',
        self::DAY_SECOND => '~^\\d+(?: \\d+(?::\\d+(?::\\d+)))$~',
        self::DAY_MICROSECOND => '~^\\d+(?: \\d+(?::\\d+(?::\\d+(?:\\.\\d+))))$~',
    ];

    public function getParts(): int
    {
        $value = $this->getValue();

        return self::$parts[$value] ?? 1;
    }

    public function getFormat(): string
    {
        $value = $this->getValue();

        return self::$formats[$value] ?? '%d';
    }

    public function getPattern(): string
    {
        $value = $this->getValue();

        return self::$patterns[$value] ?? '~^\\d+$~';
    }

    public function hasMicroseconds(): bool
    {
        return in_array($this->getValue(), [self::MICROSECOND, self::SECOND_MICROSECOND, self::MINUTE_MICROSECOND, self::HOUR_MICROSECOND, self::DAY_MICROSECOND], true);
    }

}
