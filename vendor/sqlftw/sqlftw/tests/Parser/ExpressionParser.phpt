<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../bootstrap.php';


// operators & recursion
Assert::parseSerialize("SELECT 1");
Assert::parseSerialize("SELECT 1 + 1 + 1");
Assert::parseSerialize("SELECT 1 - 1 - 1");
Assert::parseSerialize("SELECT 1 * 1 * 1");
Assert::parseSerialize("SELECT 1 / 1 / 1");
Assert::parseSerialize("SELECT 1 % 1 % 1");
Assert::parseSerialize("SELECT 1 IS NULL IS NULL");
Assert::parseSerialize("SELECT 1 IS NOT NULL IS NOT NULL");
Assert::parseSerialize("SELECT 1 IS NOT NULL IS NOT NULL IS NOT NULL");
Assert::parseSerialize("SELECT 1 IS TRUE IS TRUE");
Assert::parseSerialize("SELECT 1 IS NOT TRUE IS NOT TRUE");
Assert::parseSerialize("SELECT 1 IS FALSE IS FALSE");
Assert::parseSerialize("SELECT 1 IS NOT FALSE IS NOT FALSE");
Assert::parseSerialize("SELECT 1 OR 1 OR 1");
Assert::parseSerialize("SELECT 1 = 1 = 1");

Assert::parseSerialize("SELECT 1 + 1 * 1 > 0");
Assert::parseSerialize("SELECT @d1 := 1 * 1 > 0");
Assert::parseSerialize("SELECT d1 = 1 > 0");

Assert::parseSerialize("SELECT ( d2 = c2 % ASIN( d1 ) > i2 )");
Assert::parseSerialize("SELECT ( d2 = c2 % ASIN( d1 ) > i2 )");
