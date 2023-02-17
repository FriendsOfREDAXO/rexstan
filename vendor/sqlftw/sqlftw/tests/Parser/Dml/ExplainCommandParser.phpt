<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// {EXPLAIN | DESCRIBE | DESC} tbl_name [col_name | wild]
Assert::parseSerialize("DESCRIBE tbl1");
Assert::parseSerialize("DESC tbl1", "DESCRIBE tbl1"); // DESC -> DESCRIBE
Assert::parseSerialize("EXPLAIN tbl1", "DESCRIBE tbl1"); // EXPLAIN -> DESCRIBE
Assert::parseSerialize("DESCRIBE tbl1 tbl2");
Assert::parseSerialize("DESCRIBE tbl1 'col1%'");


// {EXPLAIN | DESCRIBE | DESC} [explain_type] {explainable_stmt | FOR CONNECTION connection_id}
Assert::parseSerialize("EXPLAIN SELECT 1");
Assert::parseSerialize("DESCRIBE SELECT 1", "EXPLAIN SELECT 1");
Assert::parseSerialize("DESC SELECT 1", "EXPLAIN SELECT 1");
Assert::parseSerialize("EXPLAIN EXTENDED SELECT 1");
Assert::parseSerialize("EXPLAIN PARTITIONS SELECT 1");
Assert::parseSerialize("EXPLAIN FORMAT=TRADITIONAL SELECT 1");
Assert::parseSerialize("EXPLAIN FORMAT=JSON SELECT 1");
Assert::parseSerialize("EXPLAIN INSERT INTO tbl1 VALUES (1)");
Assert::parseSerialize("EXPLAIN REPLACE INTO tbl1 VALUES (1)");
Assert::parseSerialize("EXPLAIN DELETE FROM tbl1");
Assert::parseSerialize("EXPLAIN UPDATE tbl1 SET col1 = 1");
Assert::parseSerialize("EXPLAIN FOR CONNECTION 123");
