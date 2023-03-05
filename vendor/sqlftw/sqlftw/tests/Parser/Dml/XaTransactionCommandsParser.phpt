<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// XA {START|BEGIN} xid [JOIN|RESUME]
Assert::parseSerialize("XA START 'tr1'");
Assert::parseSerialize("XA START 'tr1', 'tr2'");
Assert::parseSerialize("XA START 'tr1', 'tr2', 1");
Assert::parseSerialize("XA BEGIN 'tr1'", "XA START 'tr1'"); // BEGIN -> START
Assert::parseSerialize("XA START 'tr1' JOIN");
Assert::parseSerialize("XA START 'tr1' RESUME");


// XA END xid [SUSPEND [FOR MIGRATE]]
Assert::parseSerialize("XA END 'tr1'");
Assert::parseSerialize("XA END 'tr1' SUSPEND");
Assert::parseSerialize("XA END 'tr1' SUSPEND FOR MIGRATE");


// XA PREPARE xid
Assert::parseSerialize("XA PREPARE 'tr1'");


// XA COMMIT xid [ONE PHASE]
Assert::parseSerialize("XA COMMIT 'tr1'");
Assert::parseSerialize("XA COMMIT 'tr1' ONE PHASE");


// XA ROLLBACK xid
Assert::parseSerialize("XA ROLLBACK 'tr1'");


// XA RECOVER [CONVERT XID]
Assert::parseSerialize("XA RECOVER");
Assert::parseSerialize("XA RECOVER CONVERT XID");
