<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Error;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ConditionInformationItem extends SqlEnum implements InformationItem
{

    public const CLASS_ORIGIN = Keyword::CLASS_ORIGIN;
    public const SUBCLASS_ORIGIN = Keyword::SUBCLASS_ORIGIN;
    public const RETURNED_SQLSTATE = Keyword::RETURNED_SQLSTATE;
    public const MESSAGE_TEXT = Keyword::MESSAGE_TEXT;
    public const MYSQL_ERRNO = Keyword::MYSQL_ERRNO;
    public const CONSTRAINT_CATALOG = Keyword::CONSTRAINT_CATALOG;
    public const CONSTRAINT_SCHEMA = Keyword::CONSTRAINT_SCHEMA;
    public const CONSTRAINT_NAME = Keyword::CONSTRAINT_NAME;
    public const CATALOG_NAME = Keyword::CATALOG_NAME;
    public const SCHEMA_NAME = Keyword::SCHEMA_NAME;
    public const TABLE_NAME = Keyword::TABLE_NAME;
    public const COLUMN_NAME = Keyword::COLUMN_NAME;
    public const CURSOR_NAME = Keyword::CURSOR_NAME;

}
