<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Option;

use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Ddl\StorageType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\SizeLiteral;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

/**
 * @phpstan-type TableOptionValue int|bool|string|Collation|Charset|DefaultLiteral|SizeLiteral|StorageEngine|StorageType|TableCompression|TableInsertMethod|TableRowFormat|ThreeStateValue|list<ObjectIdentifier>
 */
class TableOption extends SqlEnum
{

    public const AUTOEXTEND_SIZE = Keyword::AUTOEXTEND_SIZE;
    public const AUTO_INCREMENT = Keyword::AUTO_INCREMENT;
    public const AVG_ROW_LENGTH = Keyword::AVG_ROW_LENGTH;
    public const CHARACTER_SET = Keyword::CHARACTER . ' ' . Keyword::SET;
    public const CHECKSUM = Keyword::CHECKSUM;
    public const COLLATE = Keyword::COLLATE;
    public const COMMENT = Keyword::COMMENT;
    public const COMPRESSION = Keyword::COMPRESSION;
    public const CONNECTION = Keyword::CONNECTION;
    public const DATA_DIRECTORY = Keyword::DATA . ' ' . Keyword::DIRECTORY;
    public const DELAY_KEY_WRITE = Keyword::DELAY_KEY_WRITE;
    public const ENCRYPTION = Keyword::ENCRYPTION;
    public const ENGINE = Keyword::ENGINE;
    public const INDEX_DIRECTORY = Keyword::INDEX . ' ' . Keyword::DIRECTORY;
    public const INSERT_METHOD = Keyword::INSERT_METHOD;
    public const KEY_BLOCK_SIZE = Keyword::KEY_BLOCK_SIZE;
    public const MAX_ROWS = Keyword::MAX_ROWS;
    public const MIN_ROWS = Keyword::MIN_ROWS;
    public const PACK_KEYS = Keyword::PACK_KEYS;
    public const PASSWORD = Keyword::PASSWORD;
    public const ROW_FORMAT = Keyword::ROW_FORMAT;
    public const SECONDARY_ENGINE = Keyword::SECONDARY_ENGINE;
    public const STORAGE = Keyword::STORAGE;
    public const STATS_AUTO_RECALC = Keyword::STATS_AUTO_RECALC;
    public const STATS_PERSISTENT = Keyword::STATS_PERSISTENT;
    public const STATS_SAMPLE_PAGES = Keyword::STATS_SAMPLE_PAGES;
    public const TABLESPACE = Keyword::TABLESPACE;
    public const UNION = Keyword::UNION;

    /** @var array<self::*, string|class-string> */
    private static array $types = [
        self::AUTOEXTEND_SIZE => SizeLiteral::class,
        self::AUTO_INCREMENT => BaseType::UNSIGNED,
        self::AVG_ROW_LENGTH => BaseType::UNSIGNED,
        self::CHARACTER_SET => Charset::class,
        self::CHECKSUM => BaseType::BOOL,
        self::COLLATE => Collation::class,
        self::COMMENT => BaseType::CHAR,
        self::COMPRESSION => TableCompression::class,
        self::CONNECTION => BaseType::CHAR,
        self::DATA_DIRECTORY => BaseType::CHAR,
        self::DELAY_KEY_WRITE => BaseType::BOOL,
        self::ENCRYPTION => BaseType::BOOL,
        self::ENGINE => StorageEngine::class,
        self::INDEX_DIRECTORY => BaseType::CHAR,
        self::INSERT_METHOD => TableInsertMethod::class,
        self::KEY_BLOCK_SIZE => BaseType::UNSIGNED,
        self::MAX_ROWS => BaseType::UNSIGNED,
        self::MIN_ROWS => BaseType::UNSIGNED,
        self::PACK_KEYS => ThreeStateValue::class,
        self::PASSWORD => BaseType::CHAR,
        self::ROW_FORMAT => TableRowFormat::class,
        self::SECONDARY_ENGINE => StorageEngine::class,
        self::STORAGE => StorageType::class,
        self::STATS_AUTO_RECALC => ThreeStateValue::class,
        self::STATS_PERSISTENT => ThreeStateValue::class,
        self::STATS_SAMPLE_PAGES => BaseType::UNSIGNED . '|' . DefaultLiteral::class,
        self::TABLESPACE => BaseType::CHAR,
        self::UNION => ObjectIdentifier::class . '[]',
    ];

    /**
     * @return array<self::*, string|class-string>
     */
    public static function getTypes(): array
    {
        return self::$types;
    }

}
