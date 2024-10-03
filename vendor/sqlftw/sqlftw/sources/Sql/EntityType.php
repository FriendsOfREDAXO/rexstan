<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

class EntityType
{

    public const GENERAL = 'general'; // use in places where type of entity cannot be determined before fetching name

    // database objects
    public const SCHEMA = 'schema';
    public const TABLE = 'table';
    public const VIEW = 'view';
    public const COLUMN = 'column';
    public const INDEX = 'index';
    public const INDEX_PART = 'index part';
    public const INDEX_CACHE = 'index cache';
    public const INDEX_PARSER = 'index parser';
    public const CONSTRAINT = 'constraint';
    public const TRIGGER = 'trigger';
    public const ROUTINE = 'routine';
    public const EVENT = 'event';
    public const TABLESPACE = 'tablespace';
    public const PARTITION = 'partition';
    public const SERVER = 'server';
    public const PLUGIN = 'plugin';
    public const LOG_FILE_GROUP = 'log file group';
    public const RESOURCE_GROUP = 'resource group';
    public const SYSTEM_VARIABLE = 'system variable';
    public const USER = 'user';
    public const HOST = 'host';
    public const CHANNEL = 'channel';
    public const SRS = 'srs';
    public const CHARACTER_SET = 'character set';
    public const ENUM_VALUE = 'enum value';
    public const FIELD_COMMENT = 'field comment';
    public const TABLE_COMMENT = 'table comment';
    public const INDEX_COMMENT = 'index comment';

    // session objects
    public const USER_VARIABLE = 'user variable';
    public const USER_LOCK = 'user lock';
    public const PREPARED_STATEMENT = 'prepared statement';
    public const SAVEPOINT = 'savepoint';
    public const XA_TRANSACTION = 'xa transaction';

    // routine objects
    public const PARAMETER = 'parameter';
    public const LOCAL_VARIABLE = 'local variable';
    public const CURSOR = 'cursor';
    public const CONDITION = 'condition';

    // query parts
    public const WINDOW = 'window';
    public const CTE = 'cte';
    public const QUERY_BLOCK = 'query block';
    public const ALIAS = 'alias'; // AS alias
    public const LABEL = 'label'; // label:
    public const ODBC_EXPRESSION_IDENTIFIER = 'odbc expression identifier'; // {identifier expr}

}
