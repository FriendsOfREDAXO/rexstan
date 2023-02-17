<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// SET ...
Assert::parseSerialize("SET lock_wait_timeout = 1", 'SET @@SESSION.lock_wait_timeout = 1');
Assert::parseSerialize("SET lock_wait_timeout = 1, long_query_time = 2", 'SET @@SESSION.lock_wait_timeout = 1, @@SESSION.long_query_time = 2');
Assert::parseSerialize("SET @var1 = 1");
Assert::parseSerialize("SET @@lock_wait_timeout = 1"); // todo: inconsistent serialization

Assert::parseSerialize("SET @@SESSION.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@GLOBAL.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@PERSIST.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@PERSIST_ONLY.lock_wait_timeout = 1");

Assert::parseSerialize("SET @@session.lock_wait_timeout = 1", "SET @@SESSION.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@global.lock_wait_timeout = 1", "SET @@GLOBAL.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@persist.lock_wait_timeout = 1", "SET @@PERSIST.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@persist_only.lock_wait_timeout = 1", "SET @@PERSIST_ONLY.lock_wait_timeout = 1");

Assert::parseSerialize("SET SESSION lock_wait_timeout = 1", "SET @@SESSION.lock_wait_timeout = 1");
Assert::parseSerialize("SET GLOBAL lock_wait_timeout = 1", "SET @@GLOBAL.lock_wait_timeout = 1");
Assert::parseSerialize("SET PERSIST lock_wait_timeout = 1", "SET @@PERSIST.lock_wait_timeout = 1");
Assert::parseSerialize("SET PERSIST_ONLY lock_wait_timeout = 1", "SET @@PERSIST_ONLY.lock_wait_timeout = 1");

Assert::parseSerialize("SET @@LOCAL.lock_wait_timeout = 1", "SET @@SESSION.lock_wait_timeout = 1");
Assert::parseSerialize("SET @@local.lock_wait_timeout = 1", "SET @@SESSION.lock_wait_timeout = 1");
Assert::parseSerialize("SET LOCAL lock_wait_timeout = 1", "SET @@SESSION.lock_wait_timeout = 1");
