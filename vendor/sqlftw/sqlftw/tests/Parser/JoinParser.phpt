<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../bootstrap.php';


// unions vs brackets
Assert::parseSerialize("SELECT * FROM (SELECT * FROM tbl1) AS ali1"); // subquery in parens
Assert::parseSerialize("SELECT * FROM ((SELECT * FROM tbl1)) AS ali1"); // subquery in double parens
Assert::parseSerialize("SELECT * FROM (((SELECT * FROM tbl1))) AS ali1"); // subquery in triple parens
Assert::parseSerialize("SELECT * FROM (((SELECT * FROM tbl1 AS ali1))) AS ali2"); // parenthesize

Assert::parseSerialize("SELECT * FROM (SELECT * FROM tbl1 UNION SELECT * FROM tbl2) AS ali1");
Assert::parseSerialize("SELECT * FROM ((SELECT * FROM tbl1 UNION SELECT * FROM tbl2)) AS ali1");
Assert::parseSerialize("SELECT * FROM (((SELECT * FROM tbl1 UNION SELECT * FROM tbl2))) AS ali1");

// unbracketed cascade joins
Assert::parseSerialize("SELECT sq2_t1.col_varchar AS sq2_field1
FROM t1 AS sq2_t1 
    STRAIGHT_JOIN t2 AS sq2_t2 
        JOIN t1 AS sq2_t3 ON sq2_t3.col_varchar = sq2_t2.col_varchar_key
    ON sq2_t3.col_int = sq2_t2.pk");
