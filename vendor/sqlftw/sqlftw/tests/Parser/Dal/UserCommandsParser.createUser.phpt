<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CREATE USER [IF NOT EXISTS] user [auth_option] [, user [auth_option]] DEFAULT ROLE {NONE | ALL | role [, role ] ...}
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1, role2");
Assert::parseSerialize("CREATE USER IF NOT EXISTS usr1@host1 DEFAULT ROLE role1");

// IDENTIFIED BY 'auth_string'
Assert::parseSerialize("CREATE USER usr1@host1 IDENTIFIED BY 'auth1' DEFAULT ROLE admin");

// IDENTIFIED WITH auth_plugin
Assert::parseSerialize("CREATE USER usr1@host1 IDENTIFIED WITH 'plug1' DEFAULT ROLE admin");

// IDENTIFIED WITH auth_plugin BY 'auth_string'
Assert::parseSerialize("CREATE USER usr1@host1 IDENTIFIED WITH 'plug1' BY 'auth1' DEFAULT ROLE admin");

// IDENTIFIED WITH auth_plugin AS 'hash_string'
Assert::parseSerialize("CREATE USER usr1@host1 IDENTIFIED WITH 'plug1' AS 'hash1' DEFAULT ROLE role1");

// more users
Assert::parseSerialize("CREATE USER usr1@host1 IDENTIFIED WITH 'plug1', usr2@host2 IDENTIFIED WITH 'plug1' DEFAULT ROLE role1");

// [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE NONE");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE SSL");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE X509");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE CIPHER 'cipher1'");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE ISSUER 'issuer1'");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE SUBJECT 'subject1'");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 REQUIRE SSL AND ISSUER 'issuer1'");

// [WITH resource_option [resource_option] ...]
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 WITH MAX_QUERIES_PER_HOUR 10");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 WITH MAX_UPDATES_PER_HOUR 10");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 WITH MAX_CONNECTIONS_PER_HOUR 10");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 WITH MAX_USER_CONNECTIONS 10");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 WITH MAX_QUERIES_PER_HOUR 10 MAX_USER_CONNECTIONS 10");

// [password_option | lock_option] ...
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD EXPIRE DEFAULT");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD EXPIRE NEVER");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD EXPIRE INTERVAL 365 DAY");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD HISTORY DEFAULT");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD HISTORY 2");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD REUSE INTERVAL DEFAULT");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD REUSE INTERVAL 365 DAY");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD REQUIRE CURRENT");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD REQUIRE CURRENT DEFAULT");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD REQUIRE CURRENT OPTIONAL");

// ACCOUNT LOCK | ACCOUNT UNLOCK
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 ACCOUNT LOCK");
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 ACCOUNT UNLOCK");

// more
Assert::parseSerialize("CREATE USER usr1@host1 DEFAULT ROLE role1 PASSWORD EXPIRE DEFAULT ACCOUNT UNLOCK");
