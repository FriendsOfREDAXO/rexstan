<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CREATE [UNIQUE|FULLTEXT|SPATIAL] INDEX index_name [index_type] ON tbl_name (index_col_name, ...) [index_option] [algorithm_option | lock_option] ...
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1)");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1, col2)");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1(10), col2(20))");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1 ASC, col2 DESC, col3(20) ASC)");
Assert::parseSerialize("CREATE UNIQUE KEY idx1 ON tbl1 (col1)");
Assert::parseSerialize("CREATE FULLTEXT INDEX idx1 ON tbl1 (col1)");
Assert::parseSerialize("CREATE SPATIAL INDEX idx1 ON tbl1 (col1)");

// type
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) USING BTREE");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) USING HASH");

// options
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) KEY_BLOCK_SIZE 10");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) WITH PARSER parser1");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) COMMENT 'com1'");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) VISIBLE");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) INVISIBLE");

// ALGORITHM
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) ALGORITHM DEFAULT");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) ALGORITHM COPY");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) ALGORITHM INPLACE");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) ALGORITHM INSTANT");

// LOCK
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) LOCK DEFAULT");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) LOCK NONE");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) LOCK SHARED");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) LOCK EXCLUSIVE");
Assert::parseSerialize("CREATE INDEX idx1 ON tbl1 (col1) ALGORITHM INPLACE LOCK NONE");


// DROP INDEX index_name ON tbl_name [algorithm_option | lock_option] ...
Assert::parseSerialize("DROP INDEX idx1 ON tbl1");

// ALGORITHM
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 ALGORITHM DEFAULT");
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 ALGORITHM COPY");
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 ALGORITHM INPLACE");
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 ALGORITHM INSTANT");

// LOCK
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 LOCK DEFAULT");
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 LOCK NONE");
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 LOCK SHARED");
Assert::parseSerialize("DROP INDEX idx1 ON tbl1 LOCK EXCLUSIVE");
