<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.Functions.RequireSingleLineCall

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// PURGE { BINARY | MASTER } LOGS
Assert::parseSerialize("PURGE BINARY LOGS TO 'file.log'");
Assert::parseSerialize("PURGE BINARY LOGS BEFORE '2001-01-01 01:01:01.000000'");

Assert::parseSerialize("PURGE MASTER LOGS TO 'file.log'", "PURGE BINARY LOGS TO 'file.log'"); // MASTER -> BINARY
Assert::parseSerialize("PURGE MASTER LOGS BEFORE '2001-01-01 01:01:01.000000'", "PURGE BINARY LOGS BEFORE '2001-01-01 01:01:01.000000'"); // MASTER -> BINARY


// RESET MASTER
Assert::parseSerialize("RESET MASTER");
Assert::parseSerialize("RESET MASTER TO 123");


// START GROUP_REPLICATION
Assert::parseSerialize("START GROUP_REPLICATION");


// STOP GROUP_REPLICATION
Assert::parseSerialize("STOP GROUP_REPLICATION");
