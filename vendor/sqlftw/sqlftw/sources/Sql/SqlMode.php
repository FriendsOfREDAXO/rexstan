<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use Dogma\Enum\StringSet;
use Dogma\InvalidValueException;
use Dogma\Math\IntCalc;
use Dogma\Math\PowersOfTwo;
use function array_filter;
use function array_merge;
use function array_unique;
use function explode;
use function implode;
use function strtoupper;
use function trim;

/**
 * MySQL sql_mode
 *
 * @see: https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html
 * @see: https://mariadb.com/kb/en/sql-mode/
 */
class SqlMode extends StringSet
{

    public const ALLOW_INVALID_DATES = 'ALLOW_INVALID_DATES';
    public const ANSI_QUOTES = 'ANSI_QUOTES';
    public const ERROR_FOR_DIVISION_BY_ZERO = 'ERROR_FOR_DIVISION_BY_ZERO';
    public const HIGH_NOT_PRECEDENCE = 'HIGH_NOT_PRECEDENCE';
    public const IGNORE_SPACE = 'IGNORE_SPACE';
    public const NO_AUTO_CREATE_USER = 'NO_AUTO_CREATE_USER';
    public const NO_AUTO_VALUE_ON_ZERO = 'NO_AUTO_VALUE_ON_ZERO';
    public const NO_BACKSLASH_ESCAPES = 'NO_BACKSLASH_ESCAPES';
    public const NO_DIR_IN_CREATE = 'NO_DIR_IN_CREATE';
    public const NO_ENGINE_SUBSTITUTION = 'NO_ENGINE_SUBSTITUTION';
    public const NO_FIELD_OPTIONS = 'NO_FIELD_OPTIONS';
    public const NO_KEY_OPTIONS = 'NO_KEY_OPTIONS';
    public const NO_TABLE_OPTIONS = 'NO_TABLE_OPTIONS';
    public const NO_UNSIGNED_SUBTRACTION = 'NO_UNSIGNED_SUBTRACTION';
    public const NO_ZERO_DATE = 'NO_ZERO_DATE';
    public const NO_ZERO_IN_DATE = 'NO_ZERO_IN_DATE';
    public const ONLY_FULL_GROUP_BY = 'ONLY_FULL_GROUP_BY';
    public const PAD_CHAR_TO_FULL_LENGTH = 'PAD_CHAR_TO_FULL_LENGTH';
    public const PIPES_AS_CONCAT = 'PIPES_AS_CONCAT';
    public const REAL_AS_FLOAT = 'REAL_AS_FLOAT';
    public const STRICT_ALL_TABLES = 'STRICT_ALL_TABLES';
    public const STRICT_TRANS_TABLES = 'STRICT_TRANS_TABLES';
    public const TIME_TRUNCATE_FRACTIONAL = 'TIME_TRUNCATE_FRACTIONAL';

    public const DEFAULT = 'DEFAULT';
    public const TRADITIONAL = 'TRADITIONAL';

    public const ANSI = 'ANSI';
    public const DB2 = 'DB2';
    public const MAXDB = 'MAXDB';
    public const MSSQL = 'MSSQL';
    public const ORACLE = 'ORACLE';
    public const POSTGRESQL = 'POSTGRESQL';

    /** @deprecated old version */
    public const MYSQL323 = 'MYSQL323';
    /** @deprecated old versions */
    public const MYSQL40 = 'MYSQL40';
    /** @deprecated old versions */
    public const NOT_USED = 'NOT_USED';

    /** @var array<int, string> */
    private static array $numeric = [
        PowersOfTwo::_1 => self::REAL_AS_FLOAT,
        PowersOfTwo::_2 => self::PIPES_AS_CONCAT,
        PowersOfTwo::_4 => self::ANSI_QUOTES,
        PowersOfTwo::_8 => self::IGNORE_SPACE,
        PowersOfTwo::_16 => self::NOT_USED,
        PowersOfTwo::_32 => self::ONLY_FULL_GROUP_BY,
        PowersOfTwo::_64 => self::NO_UNSIGNED_SUBTRACTION,
        PowersOfTwo::_128 => self::NO_DIR_IN_CREATE,
        // not supported ...
        PowersOfTwo::_256K => self::ANSI,
        PowersOfTwo::_512K => self::NO_AUTO_VALUE_ON_ZERO,
        PowersOfTwo::_1M => self::NO_BACKSLASH_ESCAPES,
        PowersOfTwo::_2M => self::STRICT_TRANS_TABLES,
        PowersOfTwo::_4M => self::STRICT_ALL_TABLES,
        PowersOfTwo::_8M => self::NO_ZERO_IN_DATE,
        PowersOfTwo::_16M => self::NO_ZERO_DATE,
        PowersOfTwo::_32M => self::ALLOW_INVALID_DATES,
        PowersOfTwo::_64M => self::ERROR_FOR_DIVISION_BY_ZERO,
        PowersOfTwo::_128M => self::TRADITIONAL,
        // not supported ...
        PowersOfTwo::_512M => self::HIGH_NOT_PRECEDENCE,
        PowersOfTwo::_1G => self::NO_ENGINE_SUBSTITUTION,
        PowersOfTwo::_2G => self::PAD_CHAR_TO_FULL_LENGTH,
        PowersOfTwo::_4G => self::TIME_TRUNCATE_FRACTIONAL,
        // cannot be set ...
        // self::NO_AUTO_CREATE_USER ?
        // self::NO_FIELD_OPTIONS ?
        // self::NO_KEY_OPTIONS ?
        // self::NO_TABLE_OPTIONS ?
    ];

    /** @var array<string, list<string>> */
    private static array $groups = [
        self::TRADITIONAL => [
            self::STRICT_TRANS_TABLES,
            self::STRICT_ALL_TABLES,
            self::NO_ZERO_IN_DATE,
            self::NO_ZERO_DATE,
            self::ERROR_FOR_DIVISION_BY_ZERO,
            self::NO_AUTO_CREATE_USER,
            self::NO_ENGINE_SUBSTITUTION,
        ],
        self::ANSI => [
            self::ANSI_QUOTES,
            self::IGNORE_SPACE,
            self::PIPES_AS_CONCAT,
            self::REAL_AS_FLOAT,
            self::ONLY_FULL_GROUP_BY,
        ],
        self::DB2 => [
            self::ANSI_QUOTES,
            self::IGNORE_SPACE,
            self::PIPES_AS_CONCAT,
            self::NO_KEY_OPTIONS,
            self::NO_TABLE_OPTIONS,
            self::NO_FIELD_OPTIONS,
        ],
        self::MAXDB => [
            self::ANSI_QUOTES,
            self::IGNORE_SPACE,
            self::PIPES_AS_CONCAT,
            self::NO_KEY_OPTIONS,
            self::NO_TABLE_OPTIONS,
            self::NO_FIELD_OPTIONS,
            self::NO_AUTO_CREATE_USER,
        ],
        self::MSSQL => [
            self::ANSI_QUOTES,
            self::IGNORE_SPACE,
            self::PIPES_AS_CONCAT,
            self::NO_KEY_OPTIONS,
            self::NO_TABLE_OPTIONS,
            self::NO_FIELD_OPTIONS,
        ],
        self::ORACLE => [
            self::ANSI_QUOTES,
            self::IGNORE_SPACE,
            self::PIPES_AS_CONCAT,
            self::NO_KEY_OPTIONS,
            self::NO_TABLE_OPTIONS,
            self::NO_FIELD_OPTIONS,
            self::NO_AUTO_CREATE_USER,
        ],
        self::POSTGRESQL => [
            self::ANSI_QUOTES,
            self::IGNORE_SPACE,
            self::PIPES_AS_CONCAT,
            self::NO_KEY_OPTIONS,
            self::NO_TABLE_OPTIONS,
            self::NO_FIELD_OPTIONS,
        ],
    ];

    public static function getFromInt(int $int): self
    {
        if ($int < 0) {
            throw new InvalidDefinitionException("Invalid value for system variable @@sql_mode: $int - value must not be negative.");
        }
        $parts = [];
        foreach (IntCalc::binaryComponents($int) as $i) {
            if (isset(self::$numeric[$i])) {
                $parts[] = self::$numeric[$i];
            } else {
                throw new InvalidDefinitionException("Invalid value for system variable @@sql_mode: $int - unknown component $i.");
            }
        }

        return self::getFromString(implode(',', $parts));
    }

    public static function getFromString(string $string): self
    {
        $string = trim($string);
        /** @var list<string> $parts */
        $parts = explode(',', strtoupper($string));
        $parts = array_filter($parts);
        try {
            self::checkValues($parts);
        } catch (InvalidValueException $e) {
            throw new InvalidDefinitionException("Invalid value for system variable @@sql_mode: " . $string, $e);
        }
        $items = [];
        foreach ($parts as $part) {
            if (isset(self::$groups[$part])) {
                $items = array_merge($items, self::$groups[$part]);
            } else {
                $items[] = $part;
            }
        }

        return self::get(...array_unique($items));
    }

}
