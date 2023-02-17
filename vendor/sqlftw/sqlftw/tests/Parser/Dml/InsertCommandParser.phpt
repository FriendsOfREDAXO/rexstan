<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
//     [INTO] tbl_name
//     [PARTITION (partition_name, ...)]
//     [(col_name, ...)]
//     {VALUES | VALUE} ({expr | DEFAULT}, ...), (...), ...
//     [ ON DUPLICATE KEY UPDATE
//       col_name=expr [, col_name=expr] ... ]
Assert::parseSerialize("INSERT INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("INSERT INTO tbl1 VALUES (DEFAULT, DEFAULT)");
Assert::parseSerialize("INSERT tbl1 VALUES (1, 2)", "INSERT INTO tbl1 VALUES (1, 2)"); // +[INTO]
Assert::parseSerialize("INSERT LOW_PRIORITY INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("INSERT DELAYED INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("INSERT HIGH_PRIORITY INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("INSERT IGNORE INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("INSERT DELAYED IGNORE INTO tbl1 VALUES (1, 2)");

Assert::parseSerialize("INSERT INTO tbl1 PARTITION (par1, par2) VALUES (1, 2)");
Assert::parseSerialize("INSERT INTO tbl1 VALUES (1, 2) ON DUPLICATE KEY UPDATE col1 = 1, col2 = 2");

//     SET col_name={expr | DEFAULT}, ...
Assert::parseSerialize("INSERT INTO tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("INSERT INTO tbl1 SET col1 = DEFAULT, col2 = DEFAULT");

//     SELECT ...
Assert::parseSerialize("INSERT INTO tbl1 SELECT * FROM tbl2");


// REPLACE [LOW_PRIORITY | DELAYED]
//     [INTO] tbl_name
//     [PARTITION (partition_name, ...)]
//     [(col_name, ...)]
//     {VALUES | VALUE} ({expr | DEFAULT}, ...), (...), ...
Assert::parseSerialize("REPLACE INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("REPLACE INTO tbl1 VALUES (DEFAULT, DEFAULT)");
Assert::parseSerialize("REPLACE tbl1 VALUES (1, 2)", "REPLACE INTO tbl1 VALUES (1, 2)"); // +[INTO]
Assert::parseSerialize("REPLACE LOW_PRIORITY INTO tbl1 VALUES (1, 2)");
Assert::parseSerialize("REPLACE DELAYED INTO tbl1 VALUES (1, 2)");

Assert::parseSerialize("REPLACE INTO tbl1 PARTITION (par1, par2) VALUES (1, 2)");

//     SET col_name={expr | DEFAULT}, ...
Assert::parseSerialize("REPLACE INTO tbl1 SET col1 = 1, col2 = 2");
Assert::parseSerialize("REPLACE INTO tbl1 SET col1 = DEFAULT, col2 = DEFAULT");

//     SELECT ...
Assert::parseSerialize("REPLACE INTO tbl1 SELECT * FROM tbl2");
