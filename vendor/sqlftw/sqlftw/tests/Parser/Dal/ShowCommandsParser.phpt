<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// SHOW COUNT(*) ERRORS
Assert::parseSerialize("SHOW COUNT(*) ERRORS");


// SHOW COUNT(*) WARNINGS
Assert::parseSerialize("SHOW COUNT(*) WARNINGS");


// SHOW BINLOG EVENTS [IN 'log_name'] [FROM pos] [LIMIT [offset,] row_count]
Assert::parseSerialize("SHOW BINLOG EVENTS IN 'log1' FROM 123");
Assert::parseSerialize("SHOW BINLOG EVENTS IN 'log1' LIMIT 456, 123", "SHOW BINLOG EVENTS IN 'log1' LIMIT 123 OFFSET 456");
Assert::parseSerialize("SHOW BINLOG EVENTS IN 'log1' LIMIT 123 OFFSET 456");


// SHOW CHARACTER SET [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW CHARACTER SET LIKE 'chs1'");
Assert::parseSerialize("SHOW CHARACTER SET WHERE col1 = 1");


// SHOW COLLATION [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW COLLATION LIKE 'col1'");
Assert::parseSerialize("SHOW COLLATION WHERE col1 = 1");


// SHOW CREATE {DATABASE | SCHEMA} db_name
Assert::parseSerialize("SHOW CREATE DATABASE db1");
Assert::parseSerialize("SHOW CREATE SCHEMA db1", "SHOW CREATE DATABASE db1"); // SCHEMA -> DATABASE


// SHOW CREATE EVENT event_name
Assert::parseSerialize("SHOW CREATE EVENT evt1");


// SHOW CREATE FUNCTION func_name
Assert::parseSerialize("SHOW CREATE FUNCTION func1");


// SHOW CREATE PROCEDURE proc_name
Assert::parseSerialize("SHOW CREATE PROCEDURE proc1");


// SHOW CREATE TABLE tbl_name
Assert::parseSerialize("SHOW CREATE TABLE tbl1");


// SHOW CREATE TRIGGER trigger_name
Assert::parseSerialize("SHOW CREATE TRIGGER trig1");


// SHOW CREATE USER user
Assert::parseSerialize("SHOW CREATE USER usr1");


// SHOW CREATE VIEW view_name
Assert::parseSerialize("SHOW CREATE VIEW view1");


// SHOW {DATABASES | SCHEMAS} [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW DATABASES LIKE 'db1'");
Assert::parseSerialize("SHOW DATABASES WHERE col1 = 1");
Assert::parseSerialize("SHOW SCHEMAS LIKE 'db1'", "SHOW DATABASES LIKE 'db1'"); // SCHEMAS -> DATABASES
Assert::parseSerialize("SHOW SCHEMAS WHERE col1 = 1", "SHOW DATABASES WHERE col1 = 1"); // SCHEMAS -> DATABASES


// SHOW ENGINE engine_name {STATUS | MUTEX}
Assert::parseSerialize("SHOW ENGINE InnoDB STATUS");
Assert::parseSerialize("SHOW ENGINE InnoDB MUTEX");


// SHOW [STORAGE] ENGINES
Assert::parseSerialize("SHOW STORAGE ENGINES");
Assert::parseSerialize("SHOW ENGINES", "SHOW STORAGE ENGINES"); // [STORAGE]


// SHOW ERRORS [LIMIT [offset,] row_count]
Assert::parseSerialize("SHOW ERRORS");
Assert::parseSerialize("SHOW ERRORS LIMIT 10");
Assert::parseSerialize("SHOW ERRORS LIMIT 20, 10", "SHOW ERRORS LIMIT 10 OFFSET 20");
Assert::parseSerialize("SHOW ERRORS LIMIT 10 OFFSET 20");


// SHOW EVENTS [{FROM | IN} schema_name] [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW EVENTS");
Assert::parseSerialize("SHOW EVENTS FROM db1");
Assert::parseSerialize("SHOW EVENTS IN db1", "SHOW EVENTS FROM db1"); // IN -> FROM
Assert::parseSerialize("SHOW EVENTS LIKE 'evt1'");
Assert::parseSerialize("SHOW EVENTS WHERE col1 = 1");
Assert::parseSerialize("SHOW EVENTS FROM db1 LIKE 'evt1'");
Assert::parseSerialize("SHOW EVENTS FROM db1 WHERE col1 = 1");


// SHOW FUNCTION CODE func_name
Assert::parseSerialize("SHOW FUNCTION CODE func1");


// SHOW FUNCTION STATUS [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW FUNCTION STATUS LIKE 'func1'");
Assert::parseSerialize("SHOW FUNCTION STATUS WHERE col1 = 1");


// SHOW GRANTS [FOR user_or_role [USING role [, role] ...]]
Assert::parseSerialize("SHOW GRANTS");
Assert::parseSerialize("SHOW GRANTS FOR CURRENT_USER");
Assert::parseSerialize("SHOW GRANTS FOR CURRENT_USER()", "SHOW GRANTS FOR CURRENT_USER");
Assert::parseSerialize("SHOW GRANTS FOR usr1@host1");
Assert::parseSerialize("SHOW GRANTS FOR usr1@host1 USING role1");
Assert::parseSerialize("SHOW GRANTS FOR usr1@host1 USING role1, role2");


// SHOW {INDEX | INDEXES | KEYS} {FROM | IN} tbl_name [{FROM | IN} db_name] [WHERE expr]
Assert::parseSerialize("SHOW INDEXES FROM tbl1");
Assert::parseSerialize("SHOW KEYS FROM tbl1", "SHOW INDEXES FROM tbl1");
Assert::parseSerialize("SHOW INDEX FROM tbl1", "SHOW INDEXES FROM tbl1");
Assert::parseSerialize("SHOW INDEXES IN tbl1", "SHOW INDEXES FROM tbl1");
Assert::parseSerialize("SHOW INDEXES FROM tbl1 FROM db1", "SHOW INDEXES FROM db1.tbl1"); // FROM FROM -> .
Assert::parseSerialize("SHOW INDEXES FROM tbl1 IN db1", "SHOW INDEXES FROM db1.tbl1"); // FROM IN -> .
Assert::parseSerialize("SHOW INDEXES FROM db1.tbl1");
Assert::parseSerialize("SHOW INDEXES FROM tbl1 WHERE col1 = 1");


// SHOW OPEN TABLES [{FROM | IN} db_name] [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW OPEN TABLES");
Assert::parseSerialize("SHOW OPEN TABLES FROM db1");
Assert::parseSerialize("SHOW OPEN TABLES IN db1", "SHOW OPEN TABLES FROM db1"); // IN -> FROM
Assert::parseSerialize("SHOW OPEN TABLES LIKE 'tbl1'");
Assert::parseSerialize("SHOW OPEN TABLES WHERE col1 = 1");
Assert::parseSerialize("SHOW OPEN TABLES FROM db1 LIKE 'tbl1'");
Assert::parseSerialize("SHOW OPEN TABLES FROM db1 WHERE col1 = 1");


// SHOW PLUGINS
Assert::parseSerialize("SHOW PLUGINS");


// SHOW PRIVILEGES
Assert::parseSerialize("SHOW PRIVILEGES");


// SHOW PROCEDURE CODE proc_name
Assert::parseSerialize("SHOW PROCEDURE CODE proc1");


// SHOW PROCEDURE STATUS [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW PROCEDURE STATUS LIKE 'proc1'");
Assert::parseSerialize("SHOW PROCEDURE STATUS WHERE col1 = 1");


// SHOW PROFILE [type [, type] ... ] [FOR QUERY n] [LIMIT row_count [OFFSET offset]]
Assert::parseSerialize("SHOW PROFILE");
Assert::parseSerialize("SHOW PROFILE ALL");
Assert::parseSerialize("SHOW PROFILE BLOCK IO, CONTEXT SWITCHES, CPU, IPC, MEMORY, PAGE FAULTS, SOURCE, SWAPS");
Assert::parseSerialize("SHOW PROFILE FOR QUERY 123");
Assert::parseSerialize("SHOW PROFILE LIMIT 10");
Assert::parseSerialize("SHOW PROFILE LIMIT 10 OFFSET 20");


// SHOW PROFILES
Assert::parseSerialize("SHOW PROFILES");


// SHOW RELAYLOG EVENTS [IN 'log_name'] [FROM pos] [LIMIT [offset,] row_count]
Assert::parseSerialize("SHOW RELAYLOG EVENTS");
Assert::parseSerialize("SHOW RELAYLOG EVENTS IN 'log1'");
Assert::parseSerialize("SHOW RELAYLOG EVENTS FROM 10");
Assert::parseSerialize("SHOW RELAYLOG EVENTS LIMIT 10");
Assert::parseSerialize("SHOW RELAYLOG EVENTS LIMIT 20, 10", "SHOW RELAYLOG EVENTS LIMIT 10 OFFSET 20");
Assert::parseSerialize("SHOW RELAYLOG EVENTS LIMIT 10 OFFSET 20");
Assert::parseSerialize("SHOW RELAYLOG EVENTS IN 'log1' FROM 10 LIMIT 10 OFFSET 20");


// SHOW SLAVE HOSTS
Assert::parseSerialize("SHOW SLAVE HOSTS");


// SHOW SLAVE STATUS [FOR CHANNEL channel]
Assert::parseSerialize("SHOW SLAVE STATUS");
Assert::parseSerialize("SHOW SLAVE STATUS FOR CHANNEL chan1");


// SHOW TABLE STATUS [{FROM | IN} db_name] [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW TABLE STATUS");
Assert::parseSerialize("SHOW TABLE STATUS FROM db1");
Assert::parseSerialize("SHOW TABLE STATUS IN db1", "SHOW TABLE STATUS FROM db1"); // IN -> FROM
Assert::parseSerialize("SHOW TABLE STATUS LIKE 'tbl1'");
Assert::parseSerialize("SHOW TABLE STATUS WHERE col1 = 1");
Assert::parseSerialize("SHOW TABLE STATUS FROM db1 LIKE 'tbl1'");
Assert::parseSerialize("SHOW TABLE STATUS FROM db1 WHERE col1 = 1");


// SHOW TRIGGERS [{FROM | IN} db_name] [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW TRIGGERS");
Assert::parseSerialize("SHOW TRIGGERS FROM db1");
Assert::parseSerialize("SHOW TRIGGERS IN db1", "SHOW TRIGGERS FROM db1"); // IN -> FROM
Assert::parseSerialize("SHOW TRIGGERS LIKE 'tbl1'");
Assert::parseSerialize("SHOW TRIGGERS WHERE col1 = 1");
Assert::parseSerialize("SHOW TRIGGERS FROM db1 LIKE 'tbl1'");
Assert::parseSerialize("SHOW TRIGGERS FROM db1 WHERE col1 = 1");


// SHOW WARNINGS [LIMIT [offset,] row_count]
Assert::parseSerialize("SHOW WARNINGS");
Assert::parseSerialize("SHOW WARNINGS LIMIT 10");
Assert::parseSerialize("SHOW WARNINGS LIMIT 20, 10", "SHOW WARNINGS LIMIT 10 OFFSET 20");
Assert::parseSerialize("SHOW WARNINGS LIMIT 10 OFFSET 20");


// SHOW MASTER STATUS
Assert::parseSerialize("SHOW MASTER STATUS");


// SHOW {BINARY | MASTER} LOGS
Assert::parseSerialize("SHOW BINARY LOGS");
Assert::parseSerialize("SHOW MASTER LOGS", "SHOW BINARY LOGS"); // MASTER LOGS -> BINARY LOGS


// SHOW [GLOBAL | SESSION] STATUS [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW STATUS");
Assert::parseSerialize("SHOW GLOBAL STATUS");
Assert::parseSerialize("SHOW SESSION STATUS");
Assert::parseSerialize("SHOW STATUS LIKE 'stat1'");
Assert::parseSerialize("SHOW STATUS WHERE col1 = 1");


// SHOW [GLOBAL | SESSION] VARIABLES [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW VARIABLES");
Assert::parseSerialize("SHOW GLOBAL VARIABLES");
Assert::parseSerialize("SHOW SESSION VARIABLES");
Assert::parseSerialize("SHOW VARIABLES LIKE 'var1'");
Assert::parseSerialize("SHOW VARIABLES WHERE col1 = 1");


// SHOW [FULL] COLUMNS {FROM | IN} tbl_name [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW COLUMNS FROM tbl1");
Assert::parseSerialize("SHOW FULL COLUMNS FROM tbl1");
Assert::parseSerialize("SHOW COLUMNS IN tbl1", "SHOW COLUMNS FROM tbl1"); // IN -> FROM
Assert::parseSerialize("SHOW COLUMNS FROM tbl1 LIKE 'col1'");
Assert::parseSerialize("SHOW COLUMNS FROM tbl1 WHERE col1 = 1");


// SHOW [FULL] PROCESSLIST
Assert::parseSerialize("SHOW PROCESSLIST");
Assert::parseSerialize("SHOW FULL PROCESSLIST");


// SHOW [FULL] TABLES [{FROM | IN} db_name] [LIKE 'pattern' | WHERE expr]
Assert::parseSerialize("SHOW TABLES");
Assert::parseSerialize("SHOW TABLES FROM db1");
Assert::parseSerialize("SHOW FULL TABLES FROM db1");
Assert::parseSerialize("SHOW TABLES IN db1", "SHOW TABLES FROM db1"); // IN -> FROM
Assert::parseSerialize("SHOW TABLES FROM db1 LIKE 'tbl1'");
Assert::parseSerialize("SHOW TABLES FROM db1 WHERE col1 = 1");
