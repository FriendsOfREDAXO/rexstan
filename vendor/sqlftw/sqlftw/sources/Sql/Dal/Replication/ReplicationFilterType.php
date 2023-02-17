<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ReplicationFilterType extends SqlEnum
{

    public const REPLICATE_DO_DB = Keyword::REPLICATE_DO_DB;
    public const REPLICATE_IGNORE_DB = Keyword::REPLICATE_IGNORE_DB;
    public const REPLICATE_DO_TABLE = Keyword::REPLICATE_DO_TABLE;
    public const REPLICATE_IGNORE_TABLE = Keyword::REPLICATE_IGNORE_TABLE;
    public const REPLICATE_WILD_DO_TABLE = Keyword::REPLICATE_WILD_DO_TABLE;
    public const REPLICATE_WILD_IGNORE_TABLE = Keyword::REPLICATE_WILD_IGNORE_TABLE;
    public const REPLICATE_REWRITE_DB = Keyword::REPLICATE_REWRITE_DB;

    /** @var array<self::*, string> */
    private static array $itemTypes = [
        self::REPLICATE_DO_DB => BaseType::CHAR . '[]',
        self::REPLICATE_IGNORE_DB => BaseType::CHAR . '[]',
        self::REPLICATE_DO_TABLE => ObjectIdentifier::class . '[]',
        self::REPLICATE_IGNORE_TABLE => ObjectIdentifier::class . '[]',
        self::REPLICATE_WILD_DO_TABLE => BaseType::CHAR . '[]',
        self::REPLICATE_WILD_IGNORE_TABLE => BaseType::CHAR . '[]',
        self::REPLICATE_REWRITE_DB => BaseType::CHAR . '{}',
    ];

    /**
     * @return array<self::*, string>
     */
    public static function getItemTypes(): array
    {
        return self::$itemTypes;
    }

    /**
     * @param self::* $type
     */
    public static function getItemType(string $type): string
    {
        return self::$itemTypes[$type];
    }

}
