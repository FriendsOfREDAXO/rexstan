<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// UPDATE [LOW_PRIORITY] [IGNORE] table_reference
//     SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
//     [WHERE where_condition]
//     [ORDER BY ...]
//     [LIMIT row_count]
Assert::parseSerialize("UPDATE tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("UPDATE db1.tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("UPDATE tbl1 SET tbl2.col2 = 1, tbl3.col3 = 2");
Assert::parseSerialize("UPDATE tbl1 SET db2.tbl2.col2 = 1, db3.tbl3.col3 = 2");
Assert::parseSerialize("UPDATE tbl1 SET col1 = DEFAULT, col2 = DEFAULT");
Assert::parseSerialize("UPDATE LOW_PRIORITY tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("UPDATE IGNORE tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("UPDATE LOW_PRIORITY IGNORE tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("UPDATE tbl1 SET col1 = 1, col2 = 2 WHERE col3 = 2");
Assert::parseSerialize("UPDATE tbl1 SET col1 = 1, col2 = 2 ORDER BY col3, col4");
Assert::parseSerialize("UPDATE tbl1 SET col1 = 1, col2 = 2 LIMIT 10");

// UPDATE [LOW_PRIORITY] [IGNORE] table_references
//     SET col_name1={expr1|DEFAULT} [, col_name2={expr2|DEFAULT}] ...
//     [WHERE where_condition]
Assert::parseSerialize("UPDATE tbl1 JOIN tbl2 ON tbl3.col3 = tbl4.col4 SET col5 = 1, col6 = 2");
