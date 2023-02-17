<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// ALTER [DEFINER = { user | CURRENT_USER }] EVENT event_name [RENAME TO new_event_name]
Assert::parseSerialize("ALTER EVENT evt1 RENAME TO evt2");
Assert::parseSerialize("ALTER DEFINER = usr1@host1 EVENT evt1 RENAME TO evt2");
Assert::parseSerialize("ALTER DEFINER = CURRENT_USER EVENT evt1 RENAME TO evt2");

// [ON SCHEDULE schedule:]
//   {AT timestamp [+ INTERVAL interval] ... | EVERY interval}
//   [STARTS timestamp [+ INTERVAL interval] ...]
//   [ENDS timestamp [+ INTERVAL interval] ...]
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE AT '2001-02-03 04:05:06.000007'");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE AT '2001-02-03 04:05:06.000007' + INTERVAL '6' HOUR");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE EVERY '6' HOUR");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE EVERY '6' HOUR STARTS '2001-02-03 04:05:06.000007'");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE EVERY '6' HOUR STARTS '2001-02-03 04:05:06.000007' + INTERVAL '6' HOUR");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE EVERY '6' HOUR ENDS '2001-02-03 04:05:06.000007'");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE EVERY '6' HOUR ENDS '2001-02-03 04:05:06.000007' + INTERVAL '6' HOUR");
Assert::parseSerialize("ALTER EVENT evt1 ON SCHEDULE EVERY '6' HOUR STARTS '2001-02-03 04:05:06.000007' ENDS '2011-02-03 04:05:06.000007'");

// [ON COMPLETION [NOT] PRESERVE]
Assert::parseSerialize("ALTER EVENT evt1 ON COMPLETION PRESERVE");
Assert::parseSerialize("ALTER EVENT evt1 ON COMPLETION NOT PRESERVE");

// [ENABLE | DISABLE | DISABLE ON SLAVE]
Assert::parseSerialize("ALTER EVENT evt1 ENABLE");
Assert::parseSerialize("ALTER EVENT evt1 DISABLE");
Assert::parseSerialize("ALTER EVENT evt1 DISABLE ON SLAVE");

// [COMMENT 'comment']
Assert::parseSerialize("ALTER EVENT evt1 COMMENT 'com1'");

// [DO event_body]
Assert::parseSerialize("ALTER EVENT evt1 DO SELECT 1");


// CREATE [DEFINER = { user | CURRENT_USER }] EVENT [IF NOT EXISTS] event_name ON SCHEDULE schedule
//   [ON COMPLETION [NOT] PRESERVE]
//   [ENABLE | DISABLE | DISABLE ON SLAVE]
//   [COMMENT 'comment']
//   DO event_body
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1");
Assert::parseSerialize("CREATE DEFINER = usr1@host1 EVENT evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1");
Assert::parseSerialize("CREATE DEFINER = CURRENT_USER EVENT evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1");
Assert::parseSerialize(
    "CREATE DEFINER CURRENT_USER EVENT evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1",
    "CREATE DEFINER = CURRENT_USER EVENT evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1" // [=]
);
Assert::parseSerialize("CREATE EVENT IF NOT EXISTS evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR ON COMPLETION PRESERVE DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR ON COMPLETION NOT PRESERVE DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR ENABLE DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR DISABLE DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR DISABLE ON SLAVE DO SELECT 1");
Assert::parseSerialize("CREATE EVENT evt1 ON SCHEDULE EVERY '6' HOUR COMMENT 'com1' DO SELECT 1");


// DROP EVENT [IF EXISTS] event_name
Assert::parseSerialize("DROP EVENT evt1");
Assert::parseSerialize("DROP EVENT IF EXISTS evt1");
