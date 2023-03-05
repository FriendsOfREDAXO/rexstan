<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// HANDLER tbl_name OPEN [[AS] alias]
Assert::parseSerialize("HANDLER hand1 OPEN");
Assert::parseSerialize("HANDLER hand1 OPEN AS hand2");


// HANDLER tbl_name READ index_name { = | <= | >= | < | > } (value1, value2, ...) [WHERE where_condition] [LIMIT ...]
Assert::parseSerialize("HANDLER hand1 READ idx1 = (1)");
Assert::parseSerialize("HANDLER hand1 READ idx1 <= (1)");
Assert::parseSerialize("HANDLER hand1 READ idx1 >= (1)");
Assert::parseSerialize("HANDLER hand1 READ idx1 < (1)");
Assert::parseSerialize("HANDLER hand1 READ idx1 > (1)");
Assert::parseSerialize("HANDLER hand1 READ idx1 = (1, 2, 3)");
Assert::parseSerialize("HANDLER hand1 READ idx1 = (1) WHERE col1 = 1 AND col2 != 1");
Assert::parseSerialize("HANDLER hand1 READ idx1 = (1) LIMIT 1 OFFSET 10");


// HANDLER tbl_name READ index_name { FIRST | NEXT | PREV | LAST } [WHERE where_condition] [LIMIT ...]
Assert::parseSerialize("HANDLER hand1 READ idx1 FIRST");
Assert::parseSerialize("HANDLER hand1 READ idx1 NEXT");
Assert::parseSerialize("HANDLER hand1 READ idx1 PREV");
Assert::parseSerialize("HANDLER hand1 READ idx1 LAST");
Assert::parseSerialize("HANDLER hand1 READ idx1 FIRST WHERE col1 = 1 AND col2 != 1");
Assert::parseSerialize("HANDLER hand1 READ idx1 FIRST LIMIT 1 OFFSET 10");


// HANDLER tbl_name READ { FIRST | NEXT } [WHERE where_condition] [LIMIT ...]
Assert::parseSerialize("HANDLER hand1 READ FIRST");
Assert::parseSerialize("HANDLER hand1 READ NEXT");
Assert::parseSerialize("HANDLER hand1 READ FIRST WHERE col1 = 1 AND col2 != 1");
Assert::parseSerialize("HANDLER hand1 READ FIRST LIMIT 1 OFFSET 10");


// HANDLER tbl_name CLOSE
Assert::parseSerialize("HANDLER hand1 CLOSE");
