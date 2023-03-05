<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// ALGORITHM
Assert::parseSerialize("ALTER TABLE tbl1 ALGORITHM INSTANT");
Assert::parseSerialize("ALTER TABLE tbl1 ALGORITHM INPLACE");
Assert::parseSerialize("ALTER TABLE tbl1 ALGORITHM COPY");
Assert::parseSerialize("ALTER TABLE tbl1 ALGORITHM DEFAULT");

// LOCK
Assert::parseSerialize("ALTER TABLE tbl1 LOCK DEFAULT");
Assert::parseSerialize("ALTER TABLE tbl1 LOCK NONE");
Assert::parseSerialize("ALTER TABLE tbl1 LOCK SHARED");
Assert::parseSerialize("ALTER TABLE tbl1 LOCK EXCLUSIVE");

// FORCE
Assert::parseSerialize("ALTER TABLE tbl1 FORCE");

// VALIDATION
Assert::parseSerialize("ALTER TABLE tbl1 WITH VALIDATION");
Assert::parseSerialize("ALTER TABLE tbl1 WITHOUT VALIDATION");
