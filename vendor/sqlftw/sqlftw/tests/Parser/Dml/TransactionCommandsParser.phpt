<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// COMMIT [WORK] [AND [NO] CHAIN] [[NO] RELEASE]
Assert::parseSerialize("COMMIT");
Assert::parseSerialize("COMMIT WORK", "COMMIT");
Assert::parseSerialize("COMMIT AND CHAIN");
Assert::parseSerialize("COMMIT AND NO CHAIN");
Assert::parseSerialize("COMMIT RELEASE");
Assert::parseSerialize("COMMIT NO RELEASE");


// LOCK TABLES tbl_name [[AS] alias] lock_type [, tbl_name [[AS] alias] lock_type] ...
Assert::parseSerialize("LOCK TABLE tbl1 READ", "LOCK TABLES tbl1 READ");
Assert::parseSerialize("LOCK TABLES tbl1 READ");
Assert::parseSerialize("LOCK TABLES tbl1 AS lock1 READ");
Assert::parseSerialize("LOCK TABLES tbl1 READ, tbl2 READ");
Assert::parseSerialize("LOCK TABLES tbl1 AS lock1 READ, tbl2 AS lock2 READ LOCAL");
Assert::parseSerialize("LOCK TABLES tbl1 AS lock1 WRITE, tbl2 AS lock2 LOW_PRIORITY WRITE");


// RELEASE SAVEPOINT identifier
Assert::parseSerialize("RELEASE SAVEPOINT svp1");


// ROLLBACK [WORK] [AND [NO] CHAIN] [[NO] RELEASE]
Assert::parseSerialize("ROLLBACK");
Assert::parseSerialize("ROLLBACK WORK", "ROLLBACK");
Assert::parseSerialize("ROLLBACK AND CHAIN");
Assert::parseSerialize("ROLLBACK AND NO CHAIN");
Assert::parseSerialize("ROLLBACK RELEASE");
Assert::parseSerialize("ROLLBACK NO RELEASE");


// ROLLBACK [WORK] TO [SAVEPOINT] identifier
Assert::parseSerialize("ROLLBACK TO SAVEPOINT svp1");
Assert::parseSerialize("ROLLBACK WORK TO SAVEPOINT svp1", "ROLLBACK TO SAVEPOINT svp1");
Assert::parseSerialize("ROLLBACK TO svp1", "ROLLBACK TO SAVEPOINT svp1");


// SAVEPOINT identifier
Assert::parseSerialize("SAVEPOINT svp1");


// SET [GLOBAL | SESSION] TRANSACTION transaction_characteristic [, transaction_characteristic] ...
Assert::parseSerialize("SET GLOBAL TRANSACTION READ ONLY");
Assert::parseSerialize("SET SESSION TRANSACTION READ WRITE");
Assert::parseSerialize("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
Assert::parseSerialize("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
Assert::parseSerialize("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
Assert::parseSerialize("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
Assert::parseSerialize("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE, READ WRITE");


// START TRANSACTION [transaction_characteristic [, transaction_characteristic] ...]
// BEGIN [WORK]
Assert::parseSerialize("START TRANSACTION");
Assert::parseSerialize("START TRANSACTION WITH CONSISTENT SNAPSHOT");
Assert::parseSerialize("START TRANSACTION READ ONLY");
Assert::parseSerialize("START TRANSACTION READ WRITE");
Assert::parseSerialize("START TRANSACTION WITH CONSISTENT SNAPSHOT, READ WRITE");
Assert::parseSerialize("BEGIN", "START TRANSACTION");
Assert::parseSerialize("BEGIN WORK", "START TRANSACTION");


// UNLOCK TABLES
Assert::parseSerialize("UNLOCK TABLES");
