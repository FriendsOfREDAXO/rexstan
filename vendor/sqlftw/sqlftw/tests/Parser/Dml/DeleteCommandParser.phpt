<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
//    FROM tbl_name
Assert::parseSerialize("DELETE FROM tbl1");
Assert::parseSerialize("DELETE LOW_PRIORITY FROM tbl1");
Assert::parseSerialize("DELETE QUICK FROM tbl1");
Assert::parseSerialize("DELETE IGNORE FROM tbl1");
Assert::parseSerialize("DELETE LOW_PRIORITY QUICK IGNORE FROM tbl1");

//    [PARTITION (partition_name, ...)]
Assert::parseSerialize("DELETE FROM tbl1 PARTITION (par1, par2)");

//    [WHERE where_condition]
Assert::parseSerialize("DELETE FROM tbl1 WHERE col1 = 1");

//    [ORDER BY ...]
Assert::parseSerialize("DELETE FROM tbl1 ORDER BY col1, col2");

//    [LIMIT row_count]
Assert::parseSerialize("DELETE FROM tbl1 LIMIT 10");


// DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
//    tbl_name[.*] [, tbl_name[.*]] ...
//    FROM table_references
Assert::parseSerialize("DELETE tbl1, tbl2 FROM tbl1", "DELETE FROM tbl1, tbl2 USING tbl1"); // FROM -> USING


// DELETE [LOW_PRIORITY] [QUICK] [IGNORE]
//    FROM tbl_name[.*] [, tbl_name[.*]] ...
//    USING table_references
Assert::parseSerialize("DELETE FROM tbl1, tbl2 USING tbl1");
