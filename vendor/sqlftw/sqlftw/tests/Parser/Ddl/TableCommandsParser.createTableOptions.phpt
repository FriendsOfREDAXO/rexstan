<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// more
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) AUTO_INCREMENT 17, AVG_ROW_LENGTH 17");
Assert::parseSerialize(
    "CREATE TABLE tbl1 (col1 INT) AUTO_INCREMENT 17 AVG_ROW_LENGTH 17",
    "CREATE TABLE tbl1 (col1 INT) AUTO_INCREMENT 17, AVG_ROW_LENGTH 17" // [,]
);

// AUTO_INCREMENT
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) AUTO_INCREMENT 17");

// AVG_ROW_LENGTH
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) AVG_ROW_LENGTH 17");

// CHARACTER_SET
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) CHARACTER SET utf8");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) CHARACTER SET 'utf8'", "CREATE TABLE tbl1 (col1 INT) CHARACTER SET utf8"); // ['']
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) CHARSET 'utf8'", "CREATE TABLE tbl1 (col1 INT) CHARACTER SET utf8"); // CHARSET -> CHARACTER SET
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) DEFAULT CHARACTER SET utf8", "CREATE TABLE tbl1 (col1 INT) CHARACTER SET utf8"); // [DEFAULT]
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) DEFAULT CHARSET utf8", "CREATE TABLE tbl1 (col1 INT) CHARACTER SET utf8"); // [DEFAULT] CHARSET -> CHARACTER SET

// CHECKSUM
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) CHECKSUM 0");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) CHECKSUM 1");

// COLLATE
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) COLLATE ascii_general_ci");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) COLLATE 'ascii_general_ci'", "CREATE TABLE tbl1 (col1 INT) COLLATE ascii_general_ci"); // ['']
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) DEFAULT COLLATE ascii_general_ci", "CREATE TABLE tbl1 (col1 INT) COLLATE ascii_general_ci"); // [DEFAULT]

// COMMENT
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) COMMENT 'com1'");

// COMPRESSION
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) COMPRESSION 'ZLIB'");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) COMPRESSION 'LZ4'");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) COMPRESSION 'NONE'");

// CONNECTION
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) CONNECTION 'con1'");

// DATA_DIRECTORY
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) DATA DIRECTORY 'dir1'");

// DELAY_KEY_WRITE
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) DELAY_KEY_WRITE 0");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) DELAY_KEY_WRITE 1");

// ENCRYPTION
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ENCRYPTION 'Y'");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ENCRYPTION 'N'");

// ENGINE
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ENGINE InnoDB");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ENGINE 'InnoDB'", "CREATE TABLE tbl1 (col1 INT) ENGINE InnoDB"); // '...' -> ...

// INDEX_DIRECTORY
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) INDEX DIRECTORY 'dir1'");

// INSERT_METHOD
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) INSERT_METHOD NO");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) INSERT_METHOD FIRST");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) INSERT_METHOD LAST");

// KEY_BLOCK_SIZE
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) KEY_BLOCK_SIZE 17");

// MAX_ROWS
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) MAX_ROWS 17");

// MIN_ROWS
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) MIN_ROWS 17");

// PACK_KEYS
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) PACK_KEYS 0");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) PACK_KEYS 1");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) PACK_KEYS DEFAULT");

// PASSWORD
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) PASSWORD 'pwd1'");

// ROW_FORMAT DEFAULT|DYNAMIC|FIXED|COMPRESSED|REDUNDANT|COMPACT
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ROW_FORMAT DEFAULT");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ROW_FORMAT DYNAMIC");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ROW_FORMAT FIXED");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ROW_FORMAT COMPRESSED");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ROW_FORMAT REDUNDANT");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) ROW_FORMAT COMPACT");

// STATS_AUTO_RECALC
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_AUTO_RECALC 0");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_AUTO_RECALC 1");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_AUTO_RECALC DEFAULT");

// STATS_PERSISTENT
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_PERSISTENT 0");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_PERSISTENT 1");
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_PERSISTENT DEFAULT");

// STATS_SAMPLE_PAGES
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) STATS_SAMPLE_PAGES 17");

// TABLESPACE
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) TABLESPACE tbs1");

// UNION
Assert::parseSerialize("CREATE TABLE tbl1 (col1 INT) UNION (tbl2, tbl3)");
