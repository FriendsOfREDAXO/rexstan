<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// ALTER FUNCTION func_name [characteristic ...]
Assert::parseSerialize("ALTER FUNCTION func1");
Assert::parseSerialize("ALTER FUNCTION func1 COMMENT 'com1'");
Assert::parseSerialize("ALTER FUNCTION func1 LANGUAGE SQL");
Assert::parseSerialize("ALTER FUNCTION func1 CONTAINS SQL");
Assert::parseSerialize("ALTER FUNCTION func1 NO SQL");
Assert::parseSerialize("ALTER FUNCTION func1 READS SQL DATA");
Assert::parseSerialize("ALTER FUNCTION func1 MODIFIES SQL DATA");
Assert::parseSerialize("ALTER FUNCTION func1 SQL SECURITY DEFINER");
Assert::parseSerialize("ALTER FUNCTION func1 SQL SECURITY INVOKER");

Assert::parseSerialize("ALTER FUNCTION func1 LANGUAGE SQL COMMENT 'com1'");
Assert::parseSerialize("ALTER FUNCTION func1 COMMENT 'com1' LANGUAGE SQL", "ALTER FUNCTION func1 LANGUAGE SQL COMMENT 'com1'"); // LANG <-> COM


// ALTER PROCEDURE proc_name [characteristic ...]
Assert::parseSerialize("ALTER PROCEDURE proc1");
Assert::parseSerialize("ALTER PROCEDURE proc1 COMMENT 'com1'");
Assert::parseSerialize("ALTER PROCEDURE proc1 LANGUAGE SQL");
Assert::parseSerialize("ALTER PROCEDURE proc1 CONTAINS SQL");
Assert::parseSerialize("ALTER PROCEDURE proc1 NO SQL");
Assert::parseSerialize("ALTER PROCEDURE proc1 READS SQL DATA");
Assert::parseSerialize("ALTER PROCEDURE proc1 MODIFIES SQL DATA");
Assert::parseSerialize("ALTER PROCEDURE proc1 SQL SECURITY DEFINER");
Assert::parseSerialize("ALTER PROCEDURE proc1 SQL SECURITY INVOKER");

Assert::parseSerialize("ALTER PROCEDURE proc1 LANGUAGE SQL COMMENT 'com1'");
Assert::parseSerialize("ALTER PROCEDURE proc1 COMMENT 'com1' LANGUAGE SQL", "ALTER PROCEDURE proc1 LANGUAGE SQL COMMENT 'com1'"); // LANG <-> COM


// CREATE [DEFINER = { user | CURRENT_USER }] FUNCTION sp_name ([func_parameter[, ...]]) RETURNS type [characteristic ...] routine_body
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE DEFINER = CURRENT_USER FUNCTION func1() RETURNS INT BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE DEFINER = usr1@host1 FUNCTION func1() RETURNS INT BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1(arg1 INT) RETURNS INT BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1(arg1 INT, arg2 CHAR(3)) RETURNS INT BEGIN RETURN 1; END", null, null, ';;');

Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT COMMENT 'com1' BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT LANGUAGE SQL BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT CONTAINS SQL BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT NO SQL BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT READS SQL DATA BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT MODIFIES SQL DATA BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT SQL SECURITY DEFINER BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT SQL SECURITY INVOKER BEGIN RETURN 1; END", null, null, ';;');

Assert::parseSerialize("CREATE FUNCTION func1() RETURNS INT LANGUAGE SQL COMMENT 'com1' BEGIN RETURN 1; END", null, null, ';;');
Assert::parseSerialize(
    "CREATE FUNCTION func1() RETURNS INT COMMENT 'com1' LANGUAGE SQL BEGIN RETURN 1; END",
    "CREATE FUNCTION func1() RETURNS INT LANGUAGE SQL COMMENT 'com1' BEGIN RETURN 1; END", // LANG <-> COM
    null,
    ';;'
);


// CREATE [DEFINER = { user | CURRENT_USER }] PROCEDURE sp_name ([proc_parameter[, ...]]) [characteristic ...] routine_body
Assert::parseSerialize("CREATE PROCEDURE proc1() BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE DEFINER = CURRENT_USER PROCEDURE proc1() BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE DEFINER = usr1@host1 PROCEDURE proc1() BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1(arg1 INT) BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1(arg1 INT, arg2 CHAR(3)) BEGIN SELECT 1; END", null, null, ';;');

Assert::parseSerialize("CREATE PROCEDURE proc1() COMMENT 'com1' BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() LANGUAGE SQL BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() CONTAINS SQL BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() NO SQL BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() READS SQL DATA BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() MODIFIES SQL DATA BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() SQL SECURITY DEFINER BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize("CREATE PROCEDURE proc1() SQL SECURITY INVOKER BEGIN SELECT 1; END", null, null, ';;');

Assert::parseSerialize("CREATE PROCEDURE proc1() LANGUAGE SQL COMMENT 'com1' BEGIN SELECT 1; END", null, null, ';;');
Assert::parseSerialize(
    "CREATE PROCEDURE proc1() COMMENT 'com1' LANGUAGE SQL BEGIN SELECT 1; END",
    "CREATE PROCEDURE proc1() LANGUAGE SQL COMMENT 'com1' BEGIN SELECT 1; END", // LANG <-> COM
    null,
    ';;'
);


// DROP FUNCTION [IF EXISTS] sp_name
Assert::parseSerialize("DROP FUNCTION func1");
Assert::parseSerialize("DROP FUNCTION IF EXISTS func1");


// DROP PROCEDURE [IF EXISTS] sp_name
Assert::parseSerialize("DROP PROCEDURE proc1");
Assert::parseSerialize("DROP PROCEDURE IF EXISTS proc1");
