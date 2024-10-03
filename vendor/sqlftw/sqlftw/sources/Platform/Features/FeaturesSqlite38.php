<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform\Features;

use SqlFtw\Sql\Keyword;

class FeaturesSqlite38 //extends FeaturesList
{

    public const RESERVED_WORDS = [
        Keyword::ABORT,
        Keyword::ACTION,
        Keyword::ADD,
        Keyword::AFTER,
        Keyword::ALL,
        Keyword::ALTER,
        Keyword::ANALYZE,
        Keyword::AND,
        Keyword::AS,
        Keyword::ASC,
        Keyword::ATTACH,
        Keyword::AUTOINCREMENT,
        Keyword::BEFORE,
        Keyword::BEGIN,
        Keyword::BETWEEN,
        Keyword::BY,
        Keyword::CASCADE,
        Keyword::CASE,
        Keyword::CAST,
        Keyword::CHECK,
        Keyword::COLLATE,
        Keyword::COLUMN,
        Keyword::COMMIT,
        Keyword::CONFLICT,
        Keyword::CONSTRAINT,
        Keyword::CREATE,
        Keyword::CROSS,
        Keyword::CURRENT_DATE,
        Keyword::CURRENT_TIME,
        Keyword::CURRENT_TIMESTAMP,
        Keyword::DATABASE,
        Keyword::DEFAULT,
        Keyword::DEFERRABLE,
        Keyword::DEFERRED,
        Keyword::DELETE,
        Keyword::DESC,
        Keyword::DETACH,
        Keyword::DISTINCT,
        Keyword::DROP,
        Keyword::EACH,
        Keyword::ELSE,
        Keyword::END,
        Keyword::ESCAPE,
        Keyword::EXCEPT,
        Keyword::EXCLUSIVE,
        Keyword::EXISTS,
        Keyword::EXPLAIN,
        Keyword::FAIL,
        Keyword::FOR,
        Keyword::FOREIGN,
        Keyword::FROM,
        Keyword::FULL,
        Keyword::GLOB,
        Keyword::GROUP,
        Keyword::HAVING,
        Keyword::IF,
        Keyword::IGNORE,
        Keyword::IMMEDIATE,
        Keyword::IN,
        Keyword::INDEX,
        Keyword::INDEXED,
        Keyword::INITIALLY,
        Keyword::INNER,
        Keyword::INSERT,
        Keyword::INSTEAD,
        Keyword::INTERSECT,
        Keyword::INTO,
        Keyword::IS,
        Keyword::ISNULL,
        Keyword::JOIN,
        Keyword::KEY,
        Keyword::LEFT,
        Keyword::LIKE,
        Keyword::LIMIT,
        Keyword::MATCH,
        Keyword::NATURAL,
        Keyword::NO,
        Keyword::NOT,
        Keyword::NOTNULL,
        Keyword::NULL,
        Keyword::OF,
        Keyword::OFFSET,
        Keyword::ON,
        Keyword::OR,
        Keyword::ORDER,
        Keyword::OUTER,
        Keyword::PLAN,
        Keyword::PRAGMA,
        Keyword::PRIMARY,
        Keyword::QUERY,
        Keyword::RAISE,
        Keyword::RECURSIVE,
        Keyword::REFERENCES,
        Keyword::REGEXP,
        Keyword::REINDEX,
        Keyword::RELEASE,
        Keyword::RENAME,
        Keyword::REPLACE,
        Keyword::RESTRICT,
        Keyword::RIGHT,
        Keyword::ROLLBACK,
        Keyword::ROW,
        Keyword::SAVEPOINT,
        Keyword::SELECT,
        Keyword::SET,
        Keyword::TABLE,
        Keyword::TEMP,
        Keyword::TEMPORARY,
        Keyword::THEN,
        Keyword::TO,
        Keyword::TRANSACTION,
        Keyword::TRIGGER,
        Keyword::UNION,
        Keyword::UNIQUE,
        Keyword::UPDATE,
        Keyword::USING,
        Keyword::VACUUM,
        Keyword::VALUES,
        Keyword::VIEW,
        Keyword::VIRTUAL,
        Keyword::WHEN,
        Keyword::WHERE,
        Keyword::WITH,
        Keyword::WITHOUT,
    ];

    public const NON_RESERVED_WORDS = [];

    /**
     * @return list<string>
     */
    public function getReservedWords(): array
    {
        return self::RESERVED_WORDS;
    }

    /**
     * @return list<string>
     */
    public function getNonReservedWords(): array
    {
        return self::NON_RESERVED_WORDS;
    }

    /**
     * @return list<string>
     */
    public function getOperators(): array
    {
        // todo
        return [];
    }

    /**
     * @return list<string>
     */
    public function getTypes(): array
    {
        // todo
        return [];
    }

    /**
     * @return list<string>
     */
    public function getTypeAliases(): array
    {
        // todo
        return [];
    }

    /**
     * @return list<string>
     */
    public function getBuiltInFunctions(): array
    {
        // todo
        return [];
    }

    /**
     * @return list<string>
     */
    public function getSystemVariables(): array
    {
        // todo
        return [];
    }

}
