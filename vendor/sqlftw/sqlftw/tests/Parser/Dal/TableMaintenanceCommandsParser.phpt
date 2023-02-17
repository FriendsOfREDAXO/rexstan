<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// ANALYZE [NO_WRITE_TO_BINLOG | LOCAL] TABLE tbl_name [, tbl_name] ...
Assert::parseSerialize("ANALYZE TABLE tbl1");
Assert::parseSerialize("ANALYZE TABLE tbl1, tbl2");
Assert::parseSerialize("ANALYZE NO_WRITE_TO_BINLOG TABLE tbl1", "ANALYZE LOCAL TABLE tbl1"); // NO_WRITE_TO_BINLOG -> LOCAL
Assert::parseSerialize("ANALYZE LOCAL TABLE tbl1");


// CHECK TABLE tbl_name [, tbl_name] ... [option] ...
Assert::parseSerialize("CHECK TABLE tbl1");
Assert::parseSerialize("CHECK TABLE tbl1, tbl2");
Assert::parseSerialize("CHECK TABLE tbl1 FOR UPGRADE");
Assert::parseSerialize("CHECK TABLE tbl1 QUICK");
Assert::parseSerialize("CHECK TABLE tbl1 FAST");
Assert::parseSerialize("CHECK TABLE tbl1 MEDIUM");
Assert::parseSerialize("CHECK TABLE tbl1 EXTENDED");
Assert::parseSerialize("CHECK TABLE tbl1 CHANGED");


// CHECKSUM TABLE tbl_name [, tbl_name] ... [QUICK | EXTENDED]
Assert::parseSerialize("CHECKSUM TABLE tbl1");
Assert::parseSerialize("CHECKSUM TABLE tbl1, tbl2");
Assert::parseSerialize("CHECKSUM TABLE tbl1 QUICK");
Assert::parseSerialize("CHECKSUM TABLE tbl1 EXTENDED");


// OPTIMIZE [NO_WRITE_TO_BINLOG | LOCAL] TABLE tbl_name [, tbl_name] ...
Assert::parseSerialize("OPTIMIZE TABLE tbl1");
Assert::parseSerialize("OPTIMIZE TABLE tbl1, tbl2");
Assert::parseSerialize("OPTIMIZE NO_WRITE_TO_BINLOG TABLE tbl1", "OPTIMIZE LOCAL TABLE tbl1"); // NO_WRITE_TO_BINLOG -> LOCAL
Assert::parseSerialize("OPTIMIZE LOCAL TABLE tbl1");


// REPAIR [NO_WRITE_TO_BINLOG | LOCAL] TABLE tbl_name [, tbl_name] ... [QUICK] [EXTENDED] [USE_FRM]
Assert::parseSerialize("REPAIR TABLE tbl1");
Assert::parseSerialize("REPAIR TABLE tbl1, tbl2");
Assert::parseSerialize("REPAIR NO_WRITE_TO_BINLOG TABLE tbl1", "REPAIR LOCAL TABLE tbl1"); // NO_WRITE_TO_BINLOG -> LOCAL
Assert::parseSerialize("REPAIR LOCAL TABLE tbl1");
Assert::parseSerialize("REPAIR TABLE tbl1 QUICK");
Assert::parseSerialize("REPAIR TABLE tbl1 QUICK USE_FRM");
Assert::parseSerialize("REPAIR TABLE tbl1 QUICK EXTENDED");
Assert::parseSerialize("REPAIR TABLE tbl1 QUICK EXTENDED USE_FRM");
Assert::parseSerialize("REPAIR TABLE tbl1 USE_FRM");
Assert::parseSerialize("REPAIR TABLE tbl1 EXTENDED");
Assert::parseSerialize("REPAIR TABLE tbl1 EXTENDED USE_FRM");
