<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// GRANT priv_type [(column_list)] [, priv_type [(column_list)]]... ON [object_type] priv_level TO user_or_role [, user_or_role] ...
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1");
Assert::parseSerialize("GRANT ALL ON *.* TO usr1@host1");
Assert::parseSerialize("GRANT ALL ON db1 TO usr1@host1");
Assert::parseSerialize("GRANT ALL ON db1.* TO usr1@host1");
Assert::parseSerialize("GRANT ALL ON db1.tbl1 TO usr1@host1");
Assert::parseSerialize("GRANT SELECT ON TABLE tbl1 TO usr1@host1");
Assert::parseSerialize("GRANT EXECUTE ON FUNCTION func1 TO usr1@host1");
Assert::parseSerialize("GRANT EXECUTE ON PROCEDURE proc1 TO usr1@host1");

// privileges
Assert::parseSerialize("GRANT ALTER ON * TO usr1@host1");
Assert::parseSerialize("GRANT ALTER ROUTINE ON * TO usr1@host1");
Assert::parseSerialize("GRANT CREATE ON * TO usr1@host1");
Assert::parseSerialize("GRANT CREATE ROUTINE ON * TO usr1@host1");
Assert::parseSerialize("GRANT CREATE TABLESPACE ON * TO usr1@host1");
Assert::parseSerialize("GRANT CREATE TEMPORARY TABLES ON * TO usr1@host1");
Assert::parseSerialize("GRANT CREATE USER ON * TO usr1@host1");
Assert::parseSerialize("GRANT CREATE VIEW ON * TO usr1@host1");
Assert::parseSerialize("GRANT DELETE ON * TO usr1@host1");
Assert::parseSerialize("GRANT DROP ON * TO usr1@host1");
Assert::parseSerialize("GRANT EVENT ON * TO usr1@host1");
Assert::parseSerialize("GRANT EXECUTE ON * TO usr1@host1");
Assert::parseSerialize("GRANT FILE ON * TO usr1@host1");
Assert::parseSerialize("GRANT GRANT OPTION ON * TO usr1@host1");
Assert::parseSerialize("GRANT INDEX ON * TO usr1@host1");
Assert::parseSerialize("GRANT INSERT ON * TO usr1@host1");
Assert::parseSerialize("GRANT LOCK TABLES ON * TO usr1@host1");
Assert::parseSerialize("GRANT PROCESS ON * TO usr1@host1");
Assert::parseSerialize("GRANT REFERENCES ON * TO usr1@host1");
Assert::parseSerialize("GRANT RELOAD ON * TO usr1@host1");
Assert::parseSerialize("GRANT REPLICATION CLIENT ON * TO usr1@host1");
Assert::parseSerialize("GRANT REPLICATION SLAVE ON * TO usr1@host1");
Assert::parseSerialize("GRANT SELECT ON * TO usr1@host1");
Assert::parseSerialize("GRANT SHOW DATABASES ON * TO usr1@host1");
Assert::parseSerialize("GRANT SHOW VIEW ON * TO usr1@host1");
Assert::parseSerialize("GRANT SHUTDOWN ON * TO usr1@host1");
Assert::parseSerialize("GRANT SUPER ON * TO usr1@host1");
Assert::parseSerialize("GRANT TRIGGER ON * TO usr1@host1");
Assert::parseSerialize("GRANT UPDATE ON * TO usr1@host1");
Assert::parseSerialize("GRANT USAGE ON * TO usr1@host1");
Assert::parseSerialize("GRANT SELECT, UPDATE ON * TO usr1@host1");
Assert::parseSerialize("GRANT SELECT (col1), UPDATE (col2, col3) ON * TO usr1@host1");

// WITH GRANT OPTION
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 WITH GRANT OPTION");

// AS
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 AS usr2@host2");

// WITH ROLE
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 AS usr2@host2 WITH ROLE DEFAULT");
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 AS usr2@host2 WITH ROLE NONE");
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 AS usr2@host2 WITH ROLE ALL");
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 AS usr2@host2 WITH ROLE ALL EXCEPT role1, role2");
Assert::parseSerialize("GRANT ALL ON * TO usr1@host1 AS usr2@host2 WITH ROLE role1, role2");

// todo: REQUIRE and WITH resource_option from MySQL 5


// GRANT PROXY ON user TO user [, user] ... [WITH GRANT OPTION]
Assert::parseSerialize("GRANT PROXY ON usr1@host1 TO usr2@host2");
Assert::parseSerialize("GRANT PROXY ON usr1@host1 TO usr2@host2, usr3@host3");
Assert::parseSerialize("GRANT PROXY ON usr1@host1 TO usr2@host2 WITH GRANT OPTION");


// GRANT role [, role] ... TO user [, user] ... [WITH ADMIN OPTION]
Assert::parseSerialize("GRANT role1 TO usr2@host2");
Assert::parseSerialize("GRANT role1, role2 TO usr1@host1");
Assert::parseSerialize("GRANT role1 TO usr2@host2, usr3@host3");
Assert::parseSerialize("GRANT role1 TO usr2@host2 WITH ADMIN OPTION");
