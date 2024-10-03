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

class FeaturesSql92 //extends FeaturesList
{

    public const RESERVED_WORDS = [
        Keyword::ABSOLUTE,
        Keyword::ACTION,
        Keyword::ADD,
        Keyword::ALL,
        Keyword::ALLOCATE,
        Keyword::ALTER,
        Keyword::AND,
        Keyword::ANY,
        Keyword::ARE,
        Keyword::AS,
        Keyword::ASC,
        Keyword::ASSERTION,
        Keyword::AT,
        Keyword::AUTHORIZATION,
        Keyword::AVG,
        Keyword::BEGIN,
        Keyword::BETWEEN,
        Keyword::BIT,
        Keyword::BIT_LENGTH,
        Keyword::BOTH,
        Keyword::BY,
        Keyword::CASCADE,
        Keyword::CASCADED,
        Keyword::CASE,
        Keyword::CAST,
        Keyword::CATALOG,
        Keyword::CLOSE,
        Keyword::COALESCE,
        Keyword::COLLATE,
        Keyword::COLLATION,
        Keyword::COLUMN,
        Keyword::COMMIT,
        Keyword::CONNECT,
        Keyword::CONNECTION,
        Keyword::CONSTRAINT,
        Keyword::CONSTRAINTS,
        Keyword::CONTINUE,
        Keyword::CONVERT,
        Keyword::CORRESPONDING,
        Keyword::COUNT,
        Keyword::CREATE,
        Keyword::CROSS,
        Keyword::CURRENT,
        Keyword::CURRENT_DATE,
        Keyword::CURRENT_TIME,
        Keyword::CURRENT_TIMESTAMP,
        Keyword::CURRENT_USER,
        Keyword::CURSOR,
        Keyword::DATE,
        Keyword::DAY,
        Keyword::DEALLOCATE,
        Keyword::DEC,
        Keyword::DECIMAL,
        Keyword::DECLARE,
        Keyword::DEFAULT,
        Keyword::DEFERRABLE,
        Keyword::DEFERRED,
        Keyword::DELETE,
        Keyword::DESC,
        Keyword::DESCRIBE,
        Keyword::DESCRIPTOR,
        Keyword::DIAGNOSTICS,
        Keyword::DISCONNECT,
        Keyword::DISTINCT,
        Keyword::DOMAIN,
        Keyword::DOUBLE,
        Keyword::DROP,
        Keyword::ELSE,
        Keyword::END,
        Keyword::END_EXEC,
        Keyword::ESCAPE,
        Keyword::EXCEPT,
        Keyword::EXCEPTION,
        Keyword::EXEC,
        Keyword::EXECUTE,
        Keyword::EXISTS,
        Keyword::EXTERNAL,
        Keyword::EXTRACT,
        Keyword::FALSE,
        Keyword::FETCH,
        Keyword::FIRST,
        Keyword::FLOAT,
        Keyword::FOR,
        Keyword::FOREIGN,
        Keyword::FOUND,
        Keyword::FROM,
        Keyword::FULL,
        Keyword::GET,
        Keyword::GLOBAL,
        Keyword::GO,
        Keyword::GOTO,
        Keyword::GRANT,
        Keyword::GROUP,
        Keyword::HAVING,
        Keyword::HOUR,
        Keyword::CHAR,
        Keyword::CHAR_LENGTH,
        Keyword::CHARACTER,
        Keyword::CHARACTER_LENGTH,
        Keyword::CHECK,
        Keyword::IDENTITY,
        Keyword::IMMEDIATE,
        Keyword::IN,
        Keyword::INDICATOR,
        Keyword::INITIALLY,
        Keyword::INNER,
        Keyword::INPUT,
        Keyword::INSENSITIVE,
        Keyword::INSERT,
        Keyword::INT,
        Keyword::INTEGER,
        Keyword::INTERSECT,
        Keyword::INTERVAL,
        Keyword::INTO,
        Keyword::IS,
        Keyword::ISOLATION,
        Keyword::JOIN,
        Keyword::KEY,
        Keyword::LANGUAGE,
        Keyword::LAST,
        Keyword::LEADING,
        Keyword::LEFT,
        Keyword::LEVEL,
        Keyword::LIKE,
        Keyword::LOCAL,
        Keyword::LOWER,
        Keyword::MATCH,
        Keyword::MAX,
        Keyword::MIN,
        Keyword::MINUTE,
        Keyword::MODULE,
        Keyword::MONTH,
        Keyword::NAMES,
        Keyword::NATIONAL,
        Keyword::NATURAL,
        Keyword::NEXT,
        Keyword::NCHAR,
        Keyword::NO,
        Keyword::NOT,
        Keyword::NULL,
        Keyword::NULLIF,
        Keyword::NUMERIC,
        Keyword::OCTET_LENGTH,
        Keyword::OF,
        Keyword::ON,
        Keyword::ONLY,
        Keyword::OPEN,
        Keyword::OPTION,
        Keyword::OR,
        Keyword::ORDER,
        Keyword::OUTER,
        Keyword::OUTPUT,
        Keyword::OVERLAPS,
        Keyword::PAD,
        Keyword::PARTIAL,
        Keyword::POSITION,
        Keyword::PRECISION,
        Keyword::PREPARE,
        Keyword::PRESERVE,
        Keyword::PRIMARY,
        Keyword::PRIOR,
        Keyword::PRIVILEGES,
        Keyword::PROCEDURE,
        Keyword::PUBLIC,
        Keyword::READ,
        Keyword::REAL,
        Keyword::REFERENCES,
        Keyword::RELATIVE,
        Keyword::RESTRICT,
        Keyword::REVOKE,
        Keyword::RIGHT,
        Keyword::ROLLBACK,
        Keyword::ROWS,
        Keyword::SCROLL,
        Keyword::SECOND,
        Keyword::SECTION,
        Keyword::SELECT,
        Keyword::SESSION,
        Keyword::SESSION_USER,
        Keyword::SET,
        Keyword::SCHEMA,
        Keyword::SIZE,
        Keyword::SMALLINT,
        Keyword::SOME,
        Keyword::SPACE,
        Keyword::SQL,
        Keyword::SQLCODE,
        Keyword::SQLERROR,
        Keyword::SQLSTATE,
        Keyword::SUBSTRING,
        Keyword::SUM,
        Keyword::SYSTEM_USER,
        Keyword::TABLE,
        Keyword::TEMPORARY,
        Keyword::THEN,
        Keyword::TIME,
        Keyword::TIMESTAMP,
        Keyword::TIMEZONE_HOUR,
        Keyword::TIMEZONE_MINUTE,
        Keyword::TO,
        Keyword::TRAILING,
        Keyword::TRANSACTION,
        Keyword::TRANSLATE,
        Keyword::TRANSLATION,
        Keyword::TRIM,
        Keyword::TRUE,
        Keyword::UNION,
        Keyword::UNIQUE,
        Keyword::UNKNOWN,
        Keyword::UPDATE,
        Keyword::UPPER,
        Keyword::USAGE,
        Keyword::USER,
        Keyword::USING,
        Keyword::VALUE,
        Keyword::VALUES,
        Keyword::VARCHAR,
        Keyword::VARYING,
        Keyword::VIEW,
        Keyword::WHEN,
        Keyword::WHENEVER,
        Keyword::WHERE,
        Keyword::WITH,
        Keyword::WORK,
        Keyword::WRITE,
        Keyword::YEAR,
        Keyword::ZONE,
    ];

    public const NON_RESERVED_WORDS = [
        Keyword::ADA,
        Keyword::C,
        Keyword::CATALOG_NAME,
        Keyword::CLASS_ORIGIN,
        Keyword::COBOL,
        Keyword::COLLATION_CATALOG,
        Keyword::COLLATION_NAME,
        Keyword::COLLATION_SCHEMA,
        Keyword::COLUMN_NAME,
        Keyword::COMMAND_FUNCTION,
        Keyword::COMMITTED,
        Keyword::CONDITION_NUMBER,
        Keyword::CONNECTION_NAME,
        Keyword::CONSTRAINT_CATALOG,
        Keyword::CONSTRAINT_NAME,
        Keyword::CONSTRAINT_SCHEMA,
        Keyword::CURSOR_NAME,
        Keyword::DATA,
        Keyword::DATETIME_INTERVAL_CODE,
        Keyword::DATETIME_INTERVAL_PRECISION,
        Keyword::DYNAMIC_FUNCTION,
        Keyword::FORTRAN,
        Keyword::CHARACTER_SET_CATALOG,
        Keyword::CHARACTER_SET_NAME,
        Keyword::CHARACTER_SET_SCHEMA,
        Keyword::LENGTH,
        Keyword::MESSAGE_LENGTH,
        Keyword::MESSAGE_OCTET_LENGTH,
        Keyword::MESSAGE_TEXT,
        Keyword::MORE,
        Keyword::MUMPS,
        Keyword::NAME,
        Keyword::NULLABLE,
        Keyword::NUMBER,
        Keyword::PASCAL,
        Keyword::PLI,
        Keyword::REPEATABLE,
        Keyword::RETURNED_LENGTH,
        Keyword::RETURNED_OCTET_LENGTH,
        Keyword::RETURNED_SQLSTATE,
        Keyword::ROW_COUNT,
        Keyword::SCALE,
        Keyword::SERIALIZABLE,
        Keyword::SERVER_NAME,
        Keyword::SCHEMA_NAME,
        Keyword::SUBCLASS_ORIGIN,
        Keyword::TABLE_NAME,
        Keyword::TYPE,
        Keyword::UNCOMMITTED,
        Keyword::UNNAMED,
    ];

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
