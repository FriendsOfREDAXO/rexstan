<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// https://dev.mysql.com/doc/refman/8.0/en/charset-collate.html

// With ORDER BY
Assert::parseSerialize("SELECT k FROM t1 ORDER BY k COLLATE latin1_german2_ci");

// With AS
Assert::parseSerialize("SELECT k COLLATE latin1_german2_ci AS k1 FROM t1 ORDER BY k1");

// With GROUP BY
Assert::parseSerialize("SELECT k FROM t1 GROUP BY k COLLATE latin1_german2_ci");

// With aggregate functions
Assert::parseSerialize("SELECT MAX(k COLLATE latin1_german2_ci) FROM t1");

// With DISTINCT
Assert::parseSerialize("SELECT DISTINCT k COLLATE latin1_german2_ci FROM t1");

// With WHERE
Assert::parseSerialize("SELECT * FROM t1 WHERE _latin1'Müller' COLLATE latin1_german2_ci = k");
Assert::parseSerialize("SELECT * FROM t1 WHERE k LIKE _latin1'Müller' COLLATE latin1_german2_ci");

// With HAVING
Assert::parseSerialize("SELECT k FROM t1 GROUP BY k HAVING k = _latin1'Müller' COLLATE latin1_german2_ci");
