<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CREATE ROLE [IF NOT EXISTS] role [, role ] ...
Assert::parseSerialize("CREATE ROLE role1");
Assert::parseSerialize("CREATE ROLE role1, role2");
Assert::parseSerialize("CREATE ROLE IF NOT EXISTS role1");


// DROP ROLE [IF EXISTS] role [, role ] ...
Assert::parseSerialize("DROP ROLE role1");
Assert::parseSerialize("DROP ROLE role1, role2");
Assert::parseSerialize("DROP ROLE IF EXISTS role1");


// DROP USER [IF EXISTS] user [, user] ...
Assert::parseSerialize("DROP USER usr1@host1");
Assert::parseSerialize("DROP USER usr1@host1, usr2@host2");
Assert::parseSerialize("DROP USER IF EXISTS usr1@host2");


// RENAME USER old_user TO new_user [, old_user TO new_user] ...
Assert::parseSerialize("RENAME USER usr1@host1 TO usr2@host2");
Assert::parseSerialize("RENAME USER usr1@host1 TO usr2@host2, usr3@host3 TO usr4@host4");


// SET DEFAULT ROLE {NONE | ALL | role [, role ] ...} TO user [, user ] ...
Assert::parseSerialize("SET DEFAULT ROLE NONE TO usr1@host1");
Assert::parseSerialize("SET DEFAULT ROLE ALL TO usr1@host1");
Assert::parseSerialize("SET DEFAULT ROLE role1 TO usr1@host1");
Assert::parseSerialize("SET DEFAULT ROLE role1, role2 TO usr1@host1");
Assert::parseSerialize("SET DEFAULT ROLE role1 TO usr1@host1, usr2@host2");


// SET PASSWORD [FOR user] = {PASSWORD('auth_string') | 'auth_string'}
Assert::parseSerialize("SET PASSWORD = 'pwd1'");
Assert::parseSerialize("SET PASSWORD FOR usr1@host1 = 'pwd1'");
Assert::parseSerialize("SET PASSWORD = PASSWORD('pwd1')", null, 50700);


// SET ROLE {DEFAULT | NONE | ALL | ALL EXCEPT role [, role ] ... | role [, role ] ... }
Assert::parseSerialize("SET ROLE DEFAULT");
Assert::parseSerialize("SET ROLE NONE");
Assert::parseSerialize("SET ROLE ALL");
Assert::parseSerialize("SET ROLE ALL EXCEPT role1");
Assert::parseSerialize("SET ROLE ALL EXCEPT role1, role2");
Assert::parseSerialize("SET ROLE role1");
Assert::parseSerialize("SET ROLE role1, role2");
