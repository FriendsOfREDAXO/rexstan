<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// ALTER {DATABASE | SCHEMA} [db_name] alter_specification ...
Assert::parseSerialize("ALTER SCHEMA db1 CHARACTER SET ascii");
Assert::parseSerialize("ALTER SCHEMA db1 CHARSET ascii", "ALTER SCHEMA db1 CHARACTER SET ascii"); // CHARSET -> CHARACTER SET
Assert::parseSerialize("ALTER SCHEMA db1 CHARACTER SET 'ascii'", "ALTER SCHEMA db1 CHARACTER SET ascii"); // '...' -> ...
Assert::parseSerialize("ALTER DATABASE db1 CHARACTER SET 'ascii'", "ALTER SCHEMA db1 CHARACTER SET ascii"); // DATABASE -> SCHEMA
Assert::parseSerialize("ALTER SCHEMA db1 CHARACTER SET = 'ascii'", "ALTER SCHEMA db1 CHARACTER SET ascii"); // [=]
Assert::parseSerialize("ALTER SCHEMA db1 CHARACTER SET ascii");
Assert::parseSerialize("ALTER SCHEMA db1 COLLATE ascii_general_ci");
Assert::parseSerialize("ALTER SCHEMA db1 CHARACTER SET ascii COLLATE ascii_general_ci");
Assert::parseSerialize(
    "ALTER SCHEMA db1 DEFAULT CHARACTER SET ascii DEFAULT COLLATE ascii_general_ci",  // [DEFAULT]
    "ALTER SCHEMA db1 CHARACTER SET ascii COLLATE ascii_general_ci"
);


// CREATE {DATABASE | SCHEMA} [IF NOT EXISTS] db_name [create_specification] ...
Assert::parseSerialize("CREATE SCHEMA db1 CHARACTER SET ascii");
Assert::parseSerialize("CREATE SCHEMA db1 CHARSET ascii", "CREATE SCHEMA db1 CHARACTER SET ascii"); // CHARSET -> CHARACTER SET
Assert::parseSerialize("CREATE SCHEMA db1 CHARACTER SET 'ascii'", "CREATE SCHEMA db1 CHARACTER SET ascii"); // '...' -> ...
Assert::parseSerialize("CREATE SCHEMA IF NOT EXISTS db1 CHARACTER SET ascii");
Assert::parseSerialize("CREATE DATABASE db1 CHARACTER SET ascii", "CREATE SCHEMA db1 CHARACTER SET ascii"); // DATABASE -> SCHEMA
Assert::parseSerialize("CREATE SCHEMA db1 CHARACTER SET = ascii", "CREATE SCHEMA db1 CHARACTER SET ascii"); // [=]
Assert::parseSerialize("CREATE SCHEMA db1 COLLATE ascii_general_ci");
Assert::parseSerialize("CREATE SCHEMA db1 CHARACTER SET ascii COLLATE ascii_general_ci");
Assert::parseSerialize(
    "CREATE SCHEMA db1 DEFAULT CHARACTER SET ascii DEFAULT COLLATE ascii_general_ci", // [DEFAULT]
    "CREATE SCHEMA db1 CHARACTER SET ascii COLLATE ascii_general_ci"
);


// DROP {DATABASE | SCHEMA} [IF EXISTS] db_name
Assert::parseSerialize("DROP SCHEMA db1");
Assert::parseSerialize("DROP SCHEMA IF EXISTS db1");
Assert::parseSerialize("DROP DATABASE db1", "DROP SCHEMA db1"); // DATABASE -> SCHEMA
