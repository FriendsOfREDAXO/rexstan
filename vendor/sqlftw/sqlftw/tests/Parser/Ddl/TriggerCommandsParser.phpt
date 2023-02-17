<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CREATE [DEFINER = { user | CURRENT_USER }] TRIGGER trigger_name trigger_time trigger_event ON tbl_name FOR EACH ROW [trigger_order] trigger_body
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 AFTER INSERT ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE UPDATE ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 AFTER UPDATE ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE DELETE ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 AFTER DELETE ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE DEFINER = usr1@host1 TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE DEFINER = CURRENT_USER TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW FOLLOWS trig2 SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW PRECEDES trig2 SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW UPDATE tbl2 SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW INSERT INTO tbl2 (col1) VALUES (1)");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW DELETE FROM tbl2");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW REPLACE INTO tbl2 SET col1 = 1");
Assert::parseSerialize("CREATE TRIGGER trig1 BEFORE INSERT ON tbl1 FOR EACH ROW WITH cte AS (SELECT col1 FROM tbl2) UPDATE tbl3 SET col1 = 1");


// DROP TRIGGER [IF EXISTS] [schema_name.]trigger_name
Assert::parseSerialize("DROP TRIGGER trig1");
Assert::parseSerialize("DROP TRIGGER IF EXISTS trig1");
Assert::parseSerialize("DROP TRIGGER schema1.trig1");
