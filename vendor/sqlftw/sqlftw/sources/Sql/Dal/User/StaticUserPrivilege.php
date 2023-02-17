<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\User;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class StaticUserPrivilege extends SqlEnum implements UserPrivilegeType
{

    public const ALL_PRIVILEGES = Keyword::ALL . ' ' . Keyword::PRIVILEGES;
    public const ALL = Keyword::ALL;
    public const ALTER_ROUTINE = Keyword::ALTER . ' ' . Keyword::ROUTINE;
    public const ALTER = Keyword::ALTER;
    public const CREATE_ROLE = Keyword::CREATE . ' ' . Keyword::ROLE;
    public const CREATE_ROUTINE = Keyword::CREATE . ' ' . Keyword::ROUTINE;
    public const CREATE_TABLESPACE = Keyword::CREATE . ' ' . Keyword::TABLESPACE;
    public const CREATE_TEMPORARY_TABLES = Keyword::CREATE . ' ' . Keyword::TEMPORARY . ' ' . Keyword::TABLES;
    public const CREATE_USER = Keyword::CREATE . ' ' . Keyword::USER;
    public const CREATE_VIEW = Keyword::CREATE . ' ' . Keyword::VIEW;
    public const CREATE = Keyword::CREATE;
    public const DELETE = Keyword::DELETE;
    public const DROP_ROLE = Keyword::DROP . ' ' . Keyword::ROLE;
    public const DROP = Keyword::DROP;
    public const EVENT = Keyword::EVENT;
    public const EXECUTE = Keyword::EXECUTE;
    public const FILE = Keyword::FILE;
    public const GRANT_OPTION = Keyword::GRANT . ' ' . Keyword::OPTION;
    public const INDEX = Keyword::INDEX;
    public const INSERT = Keyword::INSERT;
    public const LOCK_TABLES = Keyword::LOCK . ' ' . Keyword::TABLES;
    public const PROCESS = Keyword::PROCESS;
    public const PROXY = Keyword::PROXY;
    public const REFERENCES = Keyword::REFERENCES;
    public const RELOAD = Keyword::RELOAD;
    public const REPLICATION_CLIENT = Keyword::REPLICATION . ' ' . Keyword::CLIENT;
    public const REPLICATION_SLAVE = Keyword::REPLICATION . ' ' . Keyword::SLAVE;
    public const SELECT = Keyword::SELECT;
    public const SHOW_DATABASES = Keyword::SHOW . ' ' . Keyword::DATABASES;
    public const SHOW_VIEW = Keyword::SHOW . ' ' . Keyword::VIEW;
    public const SHUTDOWN = Keyword::SHUTDOWN;
    public const SUPER = Keyword::SUPER;
    public const TRIGGER = Keyword::TRIGGER;
    public const UPDATE = Keyword::UPDATE;
    public const USAGE = Keyword::USAGE;

}
