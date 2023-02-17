<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// REVOKE priv_type [(column_list)] [, priv_type [(column_list)]] ... ON [object_type] priv_level FROM user [, user] ...
Assert::parseSerialize("REVOKE ALTER ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE ALTER ON *.* FROM usr1@host1");
Assert::parseSerialize("REVOKE ALTER ON db1 FROM usr1@host1");
Assert::parseSerialize("REVOKE ALTER ON db1.* FROM usr1@host1");
Assert::parseSerialize("REVOKE ALTER ON db1.tbl1 FROM usr1@host1");
Assert::parseSerialize("REVOKE SELECT ON TABLE tbl1 FROM usr1@host1");
Assert::parseSerialize("REVOKE EXECUTE ON FUNCTION func1 FROM usr1@host1");
Assert::parseSerialize("REVOKE EXECUTE ON PROCEDURE proc1 FROM usr1@host1");

// privileges
Assert::parseSerialize("REVOKE ALTER ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE ALTER ROUTINE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE CREATE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE CREATE ROUTINE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE CREATE TABLESPACE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE CREATE TEMPORARY TABLES ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE CREATE USER ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE CREATE VIEW ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE DELETE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE DROP ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE EVENT ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE EXECUTE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE FILE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE GRANT OPTION ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE INDEX ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE INSERT ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE LOCK TABLES ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE PROCESS ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE REFERENCES ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE RELOAD ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE REPLICATION CLIENT ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE REPLICATION SLAVE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SELECT ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SHOW DATABASES ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SHOW VIEW ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SHUTDOWN ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SUPER ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE TRIGGER ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE UPDATE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE USAGE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SELECT, UPDATE ON * FROM usr1@host1");
Assert::parseSerialize("REVOKE SELECT (col1), UPDATE (col2, col3) ON * FROM usr1@host1");


// REVOKE ALL [PRIVILEGES], GRANT OPTION FROM user [, user] ...
Assert::parseSerialize("REVOKE ALL, GRANT OPTION FROM usr1@host1");
Assert::parseSerialize("REVOKE ALL PRIVILEGES, GRANT OPTION FROM usr1@host1", "REVOKE ALL, GRANT OPTION FROM usr1@host1");
Assert::parseSerialize("REVOKE ALL, GRANT OPTION FROM usr1@host1, usr2@host2");


// REVOKE PROXY ON user FROM user [, user] ...
Assert::parseSerialize("REVOKE PROXY ON usr1@host1 FROM usr2@host2");
Assert::parseSerialize("REVOKE PROXY ON usr1@host1 FROM usr2@host2, role3@host3");


// REVOKE role [, role] ... FROM user [, user] ...
Assert::parseSerialize("REVOKE admin FROM usr2@host2");
Assert::parseSerialize("REVOKE admin, tester FROM usr1@host1");
Assert::parseSerialize("REVOKE admin FROM usr2@host2, role3@host3");
