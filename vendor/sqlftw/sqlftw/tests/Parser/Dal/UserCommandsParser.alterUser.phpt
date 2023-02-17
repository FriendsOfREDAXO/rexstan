<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// ALTER USER [IF EXISTS] USER() IDENTIFIED BY 'auth_string'
Assert::parseSerialize("ALTER USER USER() IDENTIFIED BY 'auth1'");
Assert::parseSerialize("ALTER USER IF EXISTS USER() IDENTIFIED BY 'auth1'");


// ALTER USER [IF EXISTS] user DEFAULT ROLE {NONE | ALL | role [, role ] ...}
Assert::parseSerialize("ALTER USER usr1@host1 DEFAULT ROLE NONE");
Assert::parseSerialize("ALTER USER usr1@host1 DEFAULT ROLE ALL");
Assert::parseSerialize("ALTER USER usr1@host1 DEFAULT ROLE role1");
Assert::parseSerialize("ALTER USER usr1@host1 DEFAULT ROLE role1, role2");
Assert::parseSerialize("ALTER USER IF EXISTS usr1@host1 DEFAULT ROLE NONE");


// ALTER USER [IF EXISTS] user [auth_option] [, user [auth_option]] ...
// IDENTIFIED BY 'auth_string' [REPLACE 'current_auth_string'] [RETAIN CURRENT PASSWORD]
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED BY 'auth1'");
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED BY 'auth1' REPLACE 'auth2'");
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED BY 'auth1' REPLACE 'auth2' RETAIN CURRENT PASSWORD");
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED BY 'auth1' RETAIN CURRENT PASSWORD");

// IDENTIFIED WITH auth_plugin
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED WITH 'plug1'");

// IDENTIFIED WITH auth_plugin BY 'auth_string' [REPLACE 'current_auth_string'] [RETAIN CURRENT PASSWORD]
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED WITH 'plug1' BY 'auth1'");
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED WITH 'plug1' BY 'auth1' REPLACE 'auth2'");
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED WITH 'plug1' BY 'auth1' REPLACE 'auth2' RETAIN CURRENT PASSWORD");
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED WITH 'plug1' BY 'auth1' RETAIN CURRENT PASSWORD");

// IDENTIFIED WITH auth_plugin AS 'hash_string'
Assert::parseSerialize("ALTER USER usr1@host1 IDENTIFIED WITH 'plug1' AS 'hash1'");

// DISCARD OLD PASSWORD
Assert::parseSerialize("ALTER USER usr1@host1 DISCARD OLD PASSWORD");

// more users
Assert::parseSerialize("ALTER USER usr1@host1 DISCARD OLD PASSWORD, usr2@host2 DISCARD OLD PASSWORD");

// [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE NONE");
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE SSL");
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE X509");
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE CIPHER 'cipher1'");
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE ISSUER 'issuer1'");
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE SUBJECT 'subject1'");
Assert::parseSerialize("ALTER USER usr1@host1 REQUIRE SSL AND ISSUER 'issuer1'");

// [WITH resource_option [resource_option] ...]
Assert::parseSerialize("ALTER USER usr1@host1 WITH MAX_QUERIES_PER_HOUR 10");
Assert::parseSerialize("ALTER USER usr1@host1 WITH MAX_UPDATES_PER_HOUR 10");
Assert::parseSerialize("ALTER USER usr1@host1 WITH MAX_CONNECTIONS_PER_HOUR 10");
Assert::parseSerialize("ALTER USER usr1@host1 WITH MAX_USER_CONNECTIONS 10");
Assert::parseSerialize("ALTER USER usr1@host1 WITH MAX_QUERIES_PER_HOUR 10 MAX_USER_CONNECTIONS 10");

// [password_option | lock_option] ...
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD EXPIRE DEFAULT");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD EXPIRE NEVER");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD EXPIRE INTERVAL 365 DAY");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD HISTORY DEFAULT");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD HISTORY 2");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD REUSE INTERVAL DEFAULT");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD REUSE INTERVAL 365 DAY");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD REQUIRE CURRENT");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD REQUIRE CURRENT DEFAULT");
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD REQUIRE CURRENT OPTIONAL");

// ACCOUNT LOCK | ACCOUNT UNLOCK
Assert::parseSerialize("ALTER USER usr1@host1 ACCOUNT LOCK");
Assert::parseSerialize("ALTER USER usr1@host1 ACCOUNT UNLOCK");

// more
Assert::parseSerialize("ALTER USER usr1@host1 PASSWORD EXPIRE DEFAULT ACCOUNT UNLOCK");
