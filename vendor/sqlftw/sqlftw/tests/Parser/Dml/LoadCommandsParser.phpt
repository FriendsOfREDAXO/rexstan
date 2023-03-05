<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// LOAD DATA [LOW_PRIORITY | CONCURRENT] [LOCAL] INFILE 'file_name' ...
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD DATA LOW_PRIORITY INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD DATA CONCURRENT INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD DATA LOCAL INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD DATA INFILE 'file1' REPLACE INTO TABLE tbl1");
Assert::parseSerialize("LOAD DATA INFILE 'file1' IGNORE INTO TABLE tbl1");
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 PARTITION (p1, p2, p3)");
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 CHARACTER SET ascii");
Assert::parseSerialize(
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 CHARACTER SET 'ascii'",
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 CHARACTER SET ascii" // ['']
);
Assert::parseSerialize(
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 CHARSET ascii",
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 CHARACTER SET ascii" // CHARSET -> CHARACTER SET
);
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '~' ESCAPED BY '$'");
Assert::parseSerialize(
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 COLUMNS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '~' ESCAPED BY '$'",
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '~' ESCAPED BY '$'" // COLUMNS -> FIELDS
);
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 LINES STARTING BY ';' TERMINATED BY '~'");
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 IGNORE 10 LINES");
Assert::parseSerialize(
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 IGNORE 10 ROWS",
    "LOAD DATA INFILE 'file1' INTO TABLE tbl1 IGNORE 10 LINES"
);
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 (col1, col2)");
Assert::parseSerialize("LOAD DATA INFILE 'file1' INTO TABLE tbl1 SET col1 = 1, col2 = 2");


// LOAD XML [LOW_PRIORITY | CONCURRENT] [LOCAL] INFILE 'file_name'
Assert::parseSerialize("LOAD XML INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD XML LOW_PRIORITY INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD XML CONCURRENT INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD XML LOCAL INFILE 'file1' INTO TABLE tbl1");
Assert::parseSerialize("LOAD XML INFILE 'file1' REPLACE INTO TABLE tbl1");
Assert::parseSerialize("LOAD XML INFILE 'file1' IGNORE INTO TABLE tbl1");
Assert::parseSerialize("LOAD XML INFILE 'file1' INTO TABLE tbl1 CHARACTER SET ascii");
Assert::parseSerialize(
    "LOAD XML INFILE 'file1' INTO TABLE tbl1 CHARACTER SET 'ascii'",
    "LOAD XML INFILE 'file1' INTO TABLE tbl1 CHARACTER SET ascii" // ['']
);
Assert::parseSerialize(
    "LOAD XML INFILE 'file1' INTO TABLE tbl1 CHARSET ascii",
    "LOAD XML INFILE 'file1' INTO TABLE tbl1 CHARACTER SET ascii" // CHARSET -> CHARACTER SET
);
Assert::parseSerialize("LOAD XML INFILE 'file1' INTO TABLE tbl1 ROWS IDENTIFIED BY '<tr>'");
Assert::parseSerialize("LOAD XML INFILE 'file1' INTO TABLE tbl1 IGNORE 10 LINES");
Assert::parseSerialize(
    "LOAD XML INFILE 'file1' INTO TABLE tbl1 IGNORE 10 ROWS",
    "LOAD XML INFILE 'file1' INTO TABLE tbl1 IGNORE 10 LINES"
);
Assert::parseSerialize("LOAD XML INFILE 'file1' INTO TABLE tbl1 (col1, col2)");
Assert::parseSerialize("LOAD XML INFILE 'file1' INTO TABLE tbl1 SET col1 = 1, col2 = 2");
