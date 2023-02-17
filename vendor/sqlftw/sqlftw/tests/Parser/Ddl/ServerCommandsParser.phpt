<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// ALTER SERVER server_name OPTIONS (option [, option] ...)
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (HOST 'db.example.com')");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (DATABASE 'db1')");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (USER 'usr1')");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (PASSWORD 'pwd1')");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (SOCKET 'sock1')");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (OWNER 'usr1')");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (PORT 12345)");
Assert::parseSerialize("ALTER SERVER srv1 OPTIONS (HOST 'db.example.com', PORT 12345)");


// CREATE SERVER server_name FOREIGN DATA WRAPPER wrapper_name OPTIONS (option [, option] ...)
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (HOST 'db.example.com')");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (DATABASE 'db1')");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (USER 'usr1')");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (PASSWORD 'pwd1')");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (SOCKET 'sock1')");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (OWNER 'usr1')");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (PORT 12345)");
Assert::parseSerialize("CREATE SERVER srv1 FOREIGN DATA WRAPPER 'wrap1' OPTIONS (HOST 'db.example.com', PORT 12345)");


// DROP SERVER [ IF EXISTS ] server_name
Assert::parseSerialize("DROP SERVER srv1");
Assert::parseSerialize("DROP SERVER IF EXISTS srv1");
