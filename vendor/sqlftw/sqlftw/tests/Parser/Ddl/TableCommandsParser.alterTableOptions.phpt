<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// AUTO_INCREMENT
Assert::parseSerialize("ALTER TABLE tbl1 AUTO_INCREMENT 17");

// AVG_ROW_LENGTH
Assert::parseSerialize("ALTER TABLE tbl1 AVG_ROW_LENGTH 17");

// CHARACTER_SET
Assert::parseSerialize("ALTER TABLE tbl1 CHARACTER SET utf8");
Assert::parseSerialize("ALTER TABLE tbl1 CHARACTER SET 'utf8'", "ALTER TABLE tbl1 CHARACTER SET utf8"); // '...' -> ...
Assert::parseSerialize("ALTER TABLE tbl1 CHARSET utf8", "ALTER TABLE tbl1 CHARACTER SET utf8"); // CHARSET -> CHARACTER SET

// CHECKSUM
Assert::parseSerialize("ALTER TABLE tbl1 CHECKSUM 0");
Assert::parseSerialize("ALTER TABLE tbl1 CHECKSUM 1");

// COLLATE
Assert::parseSerialize("ALTER TABLE tbl1 COLLATE ascii_general_ci");
Assert::parseSerialize("ALTER TABLE tbl1 COLLATE 'ascii_general_ci'", "ALTER TABLE tbl1 COLLATE ascii_general_ci"); // '...' -> ...

// COMMENT
Assert::parseSerialize("ALTER TABLE tbl1 COMMENT 'ciom1'");

// COMPRESSION
Assert::parseSerialize("ALTER TABLE tbl1 COMPRESSION 'ZLIB'");
Assert::parseSerialize("ALTER TABLE tbl1 COMPRESSION 'LZ4'");
Assert::parseSerialize("ALTER TABLE tbl1 COMPRESSION 'NONE'");

// CONNECTION
Assert::parseSerialize("ALTER TABLE tbl1 CONNECTION 'con1'");

// DATA_DIRECTORY
Assert::parseSerialize("ALTER TABLE tbl1 DATA DIRECTORY 'dir1'");

// DELAY_KEY_WRITE
Assert::parseSerialize("ALTER TABLE tbl1 DELAY_KEY_WRITE 0");
Assert::parseSerialize("ALTER TABLE tbl1 DELAY_KEY_WRITE 1");

// ENCRYPTION
Assert::parseSerialize("ALTER TABLE tbl1 ENCRYPTION 'Y'");
Assert::parseSerialize("ALTER TABLE tbl1 ENCRYPTION 'N'");

// ENGINE
Assert::parseSerialize("ALTER TABLE tbl1 ENGINE InnoDB");
Assert::parseSerialize("ALTER TABLE tbl1 ENGINE 'InnoDB'", "ALTER TABLE tbl1 ENGINE InnoDB"); // '...' -> ...

// INDEX_DIRECTORY
Assert::parseSerialize("ALTER TABLE tbl1 INDEX DIRECTORY 'dir1'");

// INSERT_METHOD
Assert::parseSerialize("ALTER TABLE tbl1 INSERT_METHOD NO");
Assert::parseSerialize("ALTER TABLE tbl1 INSERT_METHOD FIRST");
Assert::parseSerialize("ALTER TABLE tbl1 INSERT_METHOD LAST");

// KEY_BLOCK_SIZE
Assert::parseSerialize("ALTER TABLE tbl1 KEY_BLOCK_SIZE 17");

// MAX_ROWS
Assert::parseSerialize("ALTER TABLE tbl1 MAX_ROWS 17");

// MIN_ROWS
Assert::parseSerialize("ALTER TABLE tbl1 MIN_ROWS 17");

// PACK_KEYS
Assert::parseSerialize("ALTER TABLE tbl1 PACK_KEYS 0");
Assert::parseSerialize("ALTER TABLE tbl1 PACK_KEYS 1");
Assert::parseSerialize("ALTER TABLE tbl1 PACK_KEYS DEFAULT");

// PASSWORD
Assert::parseSerialize("ALTER TABLE tbl1 PASSWORD 'pwd1'");

// ROW_FORMAT DEFAULT|DYNAMIC|FIXED|COMPRESSED|REDUNDANT|COMPACT
Assert::parseSerialize("ALTER TABLE tbl1 ROW_FORMAT DEFAULT");
Assert::parseSerialize("ALTER TABLE tbl1 ROW_FORMAT DYNAMIC");
Assert::parseSerialize("ALTER TABLE tbl1 ROW_FORMAT FIXED");
Assert::parseSerialize("ALTER TABLE tbl1 ROW_FORMAT COMPRESSED");
Assert::parseSerialize("ALTER TABLE tbl1 ROW_FORMAT REDUNDANT");
Assert::parseSerialize("ALTER TABLE tbl1 ROW_FORMAT COMPACT");

// STATS_AUTO_RECALC
Assert::parseSerialize("ALTER TABLE tbl1 STATS_AUTO_RECALC 0");
Assert::parseSerialize("ALTER TABLE tbl1 STATS_AUTO_RECALC 1");
Assert::parseSerialize("ALTER TABLE tbl1 STATS_AUTO_RECALC DEFAULT");

// STATS_PERSISTENT
Assert::parseSerialize("ALTER TABLE tbl1 STATS_PERSISTENT 0");
Assert::parseSerialize("ALTER TABLE tbl1 STATS_PERSISTENT 1");
Assert::parseSerialize("ALTER TABLE tbl1 STATS_PERSISTENT DEFAULT");

// STATS_SAMPLE_PAGES
Assert::parseSerialize("ALTER TABLE tbl1 STATS_SAMPLE_PAGES 17");

// TABLESPACE
Assert::parseSerialize("ALTER TABLE tbl1 TABLESPACE tbs1");

// UNION
Assert::parseSerialize("ALTER TABLE tbl1 UNION (tbl2, tbl3)");
