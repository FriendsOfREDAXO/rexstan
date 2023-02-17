<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../bootstrap.php';

// ?
Assert::parseSerialize("UPDATE tbl1 SET col1 = ? WHERE col2 = ?");

// ?123
Assert::parseSerialize("UPDATE tbl1 SET col1 = ?123 WHERE col2 = ?456");

// :var
Assert::parseSerialize("UPDATE tbl1 SET col1 = :var1 WHERE col2 = :var2");
