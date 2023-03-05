<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// SELECT
//     [ALL | DISTINCT | DISTINCTROW ]
//     [HIGH_PRIORITY]
//     [STRAIGHT_JOIN]
//     [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
//     [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
//     select_expr [, select_expr ...]
Assert::parseSerialize("SELECT col1");
Assert::parseSerialize("SELECT col1, col2");
Assert::parseSerialize("SELECT tbl1.col1, tbl2.col2");
Assert::parseSerialize("SELECT db1.tbl1.col1, db2.tbl2.col2");
Assert::parseSerialize("SELECT @var1, @var2");
Assert::parseSerialize("SELECT @@GLOBAL.basedir, @@GLOBAL.datadir");
Assert::parseSerialize("SELECT func1(), func2()");
Assert::parseSerialize("SELECT db1.func1(), db2.func2()");
Assert::parseSerialize("SELECT * FROM `DUAL`");
Assert::parseSerialize("SELECT tbl1.*, tbl2.*");
Assert::parseSerialize("SELECT db1.tbl1.*, db2.tbl2.*");

// modifiers
Assert::parseSerialize("SELECT ALL @var1, @var2");
Assert::parseSerialize("SELECT DISTINCT @var1, @var2");
Assert::parseSerialize("SELECT DISTINCTROW @var1, @var2", "SELECT DISTINCT @var1, @var2");
Assert::parseSerialize("SELECT HIGH_PRIORITY @var1, @var2");
Assert::parseSerialize("SELECT STRAIGHT_JOIN @var1, @var2");
Assert::parseSerialize("SELECT SQL_SMALL_RESULT @var1, @var2");
Assert::parseSerialize("SELECT SQL_BIG_RESULT @var1, @var2");
Assert::parseSerialize("SELECT SQL_BUFFER_RESULT @var1, @var2");
//Assert::parse("SELECT SQL_CACHE @var1, @var2"); // SQL_CACHE removed in 8.0.3
Assert::parseSerialize("SELECT SQL_NO_CACHE @var1, @var2");
Assert::parseSerialize("SELECT SQL_CALC_FOUND_ROWS @var1, @var2");

//     [FROM table_references
//       [PARTITION partition_list]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1 JOIN tbl2 ON tbl1.col3 = tbl2.col4");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 PARTITION (x, y)");

//     [WHERE where_condition]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 WHERE col1 = 1 AND col2 = 2");

//     [GROUP BY {col_name | expr | position}
//       [ASC | DESC], ... [WITH ROLLUP]]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 GROUP BY col3, col4 ASC, col5 DESC", null, 50700);
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 GROUP BY col3 IS NULL, col4 - 10 ASC, col5 < 1 DESC", null, 50700);
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 GROUP BY 1, 2 ASC, 3 DESC", null, 50700);
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 GROUP BY 1, 2 ASC, 3 DESC WITH ROLLUP", null, 50700);

//     [HAVING where_condition]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 HAVING col1 = 1 AND col2 = 2");

//     [WINDOW window_name AS (window_spec)
//       [, window_name AS (window_spec)] ...]
// todo

//     [ORDER BY {col_name | expr | position}
//       [ASC | DESC], ...]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 ORDER BY col3, col4 ASC, col5 DESC");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 ORDER BY col3 IS NULL, col4 - 10 ASC, col5 < 1 DESC");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 ORDER BY 1, 2 ASC, 3 DESC");

//     [LIMIT {[offset,] row_count | row_count OFFSET offset}]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 LIMIT 10");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 LIMIT 10 OFFSET 20");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 LIMIT 20, 10", "SELECT col1, col2 FROM tbl1, tbl2 LIMIT 10 OFFSET 20");

//     [INTO OUTFILE 'file_name'
//         [CHARACTER SET charset_name]
//         export_options
//       | INTO DUMPFILE 'file_name'
//       | INTO var_name [, var_name]]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 INTO OUTFILE 'file.txt'");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 INTO OUTFILE 'file.txt' CHARACTER SET utf8");
Assert::parseSerialize(
    "SELECT col1, col2 FROM tbl1, tbl2 INTO OUTFILE 'file.txt' CHARSET utf8",
    "SELECT col1, col2 FROM tbl1, tbl2 INTO OUTFILE 'file.txt' CHARACTER SET utf8"
);
Assert::parseSerialize(
    "SELECT col1, col2 FROM tbl1, tbl2 INTO OUTFILE 'file.txt' CHARACTER SET 'utf8'",
    "SELECT col1, col2 FROM tbl1, tbl2 INTO OUTFILE 'file.txt' CHARACTER SET utf8"
);
// todo: test for export_options
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 INTO DUMPFILE 'file.txt'");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 INTO @var1, @var2");

//     [FOR UPDATE | LOCK IN SHARE MODE]]
//     [FOR {UPDATE | SHARE} [OF tbl_name [, tbl_name] ...] [NOWAIT | SKIP LOCKED]
//       | LOCK IN SHARE MODE]]
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 LOCK IN SHARE MODE");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR UPDATE");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR SHARE");

// 8.0 features
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR UPDATE NOWAIT");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR UPDATE SKIP LOCKED");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR UPDATE OF tbl3, tbl4");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR UPDATE OF tbl3, tbl4 NOWAIT");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR UPDATE OF tbl3, tbl4 SKIP LOCKED");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR SHARE NOWAIT");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR SHARE SKIP LOCKED");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR SHARE OF tbl3, tbl4");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR SHARE OF tbl3, tbl4 NOWAIT");
Assert::parseSerialize("SELECT col1, col2 FROM tbl1, tbl2 FOR SHARE OF tbl3, tbl4 SKIP LOCKED");
