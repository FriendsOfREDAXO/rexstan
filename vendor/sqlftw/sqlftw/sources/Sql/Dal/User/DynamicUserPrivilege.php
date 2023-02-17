<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\User;

use SqlFtw\Sql\SqlEnum;

class DynamicUserPrivilege extends SqlEnum implements UserPrivilegeType
{

    public const APPLICATION_PASSWORD_ADMIN	= 'APPLICATION_PASSWORD_ADMIN';
    public const AUDIT_ABORT_EXEMPT	= 'AUDIT_ABORT_EXEMPT';
    public const AUDIT_ADMIN = 'AUDIT_ADMIN';
    public const AUTHENTICATION_POLICY_ADMIN = 'AUTHENTICATION_POLICY_ADMIN';
    public const BACKUP_ADMIN = 'BACKUP_ADMIN';
    public const BINLOG_ADMIN = 'BINLOG_ADMIN';
    public const BINLOG_ENCRYPTION_ADMIN = 'BINLOG_ENCRYPTION_ADMIN';
    public const CLONE_ADMIN = 'CLONE_ADMIN';
    public const CONNECTION_ADMIN = 'CONNECTION_ADMIN';
    public const ENCRYPTION_KEY_ADMIN = 'ENCRYPTION_KEY_ADMIN';
    public const FIREWALL_ADMIN = 'FIREWALL_ADMIN';
    public const FIREWALL_EXEMPT = 'FIREWALL_EXEMPT';
    public const FIREWALL_USER = 'FIREWALL_USER';
    public const FLUSH_OPTIMIZER_COSTS = 'FLUSH_OPTIMIZER_COSTS';
    public const FLUSH_STATUS = 'FLUSH_STATUS';
    public const FLUSH_TABLES = 'FLUSH_TABLES';
    public const FLUSH_USER_RESOURCES = 'FLUSH_USER_RESOURCES';
    public const GROUP_REPLICATION_ADMIN = 'GROUP_REPLICATION_ADMIN';
    public const GROUP_REPLICATION_STREAM = 'GROUP_REPLICATION_STREAM';
    public const INNODB_REDO_LOG_ARCHIVE = 'INNODB_REDO_LOG_ARCHIVE';
    public const INNODB_REDO_LOG_ENABLE = 'INNODB_REDO_LOG_ENABLE';
    public const NDB_STORED_USER = 'NDB_STORED_USER';
    public const PASSWORDLESS_USER_ADMIN = 'PASSWORDLESS_USER_ADMIN';
    public const PERSIST_RO_VARIABLES_ADMIN = 'PERSIST_RO_VARIABLES_ADMIN';
    public const REPLICATION_APPLIER = 'REPLICATION_APPLIER';
    public const REPLICATION_SLAVE_ADMIN = 'REPLICATION_SLAVE_ADMIN';
    public const RESOURCE_GROUP_ADMIN = 'RESOURCE_GROUP_ADMIN';
    public const RESOURCE_GROUP_USER = 'RESOURCE_GROUP_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const SENSITIVE_VARIABLES_OBSERVER = 'SENSITIVE_VARIABLES_OBSERVER';
    public const SERVICE_CONNECTION_ADMIN = 'SERVICE_CONNECTION_ADMIN';
    public const SESSION_VARIABLES_ADMIN = 'SESSION_VARIABLES_ADMIN';
    public const SET_USER_ID = 'SET_USER_ID';
    public const SHOW_ROUTINE = 'SHOW_ROUTINE';
    public const SKIP_QUERY_REWRITE = 'SKIP_QUERY_REWRITE';
    public const SYSTEM_USER = 'SYSTEM_USER';
    public const SYSTEM_VARIABLES_ADMIN = 'SYSTEM_VARIABLES_ADMIN';
    public const TABLE_ENCRYPTION_ADMIN = 'TABLE_ENCRYPTION_ADMIN';
    public const VERSION_TOKEN_ADMIN = 'VERSION_TOKEN_ADMIN';
    public const XA_RECOVER_ADMIN = 'XA_RECOVER_ADMIN';

}
