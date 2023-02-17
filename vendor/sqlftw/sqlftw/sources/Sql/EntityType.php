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

    public const SCHEMA = 'schema';
    public const TABLE = 'table';
    public const VIEW = 'view';
    public const COLUMN = 'column';
    public const INDEX = 'index';
    public const CONSTRAINT = 'constraint';
    public const TRIGGER = 'trigger';
    public const ROUTINE = 'routine';
    public const EVENT = 'event';
    public const SYSTEM_VARIABLE = 'system variable';
    public const LOCAL_VARIABLE = 'local variable';
    public const USER_VARIABLE = 'user variable';
    public const TABLESPACE = 'tablespace';
    public const PARTITION = 'partition';
    public const SERVER = 'server';
    public const LOG_FILE_GROUP = 'log file group';
    public const RESOURCE_GROUP = 'resource group';
    public const QUERY_BLOCK = 'query block';
    public const ALIAS = 'alias';
    public const LABEL = 'label';
    public const USER = 'user';
    public const HOST = 'host';
    public const XA_TRANSACTION = 'xa transaction';
    public const CHANNEL = 'channel';
    public const SRS = 'srs';
    public const ENUM_VALUE = 'enum value';
    public const FIELD_COMMENT = 'field comment';
    public const TABLE_COMMENT = 'table comment';
    public const INDEX_COMMENT = 'index comment';

    // other named objects:
    // - routine parameter
    // - index part
    // - cursor
    // - prepared statement
    // - window
    // - CTE
    // - condition
    // - savepoint
    // - user lock

}
