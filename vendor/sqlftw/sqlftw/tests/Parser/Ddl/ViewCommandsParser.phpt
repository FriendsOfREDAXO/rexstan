<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// ALTER VIEW view_name [(column_list)] AS select_statement
Assert::parseSerialize("ALTER VIEW view1 AS SELECT 1");
Assert::parseSerialize("ALTER VIEW view1 (col1) AS SELECT 1");
Assert::parseSerialize("ALTER VIEW view1 (col1, col2) AS SELECT 1");

// [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
Assert::parseSerialize("ALTER ALGORITHM = UNDEFINED VIEW view1 AS SELECT 1");
Assert::parseSerialize("ALTER ALGORITHM = MERGE VIEW view1 AS SELECT 1");
Assert::parseSerialize("ALTER ALGORITHM = TEMPTABLE VIEW view1 AS SELECT 1");

// [DEFINER = { user | CURRENT_USER }]
Assert::parseSerialize("ALTER DEFINER = usr1@host1 VIEW view1 AS SELECT 1");
Assert::parseSerialize("ALTER DEFINER = CURRENT_USER VIEW view1 AS SELECT 1");

// [SQL SECURITY { DEFINER | INVOKER }]
Assert::parseSerialize("ALTER SQL SECURITY DEFINER VIEW view1 AS SELECT 1");
Assert::parseSerialize("ALTER SQL SECURITY INVOKER VIEW view1 AS SELECT 1");

// [WITH [CASCADED | LOCAL] CHECK OPTION]
Assert::parseSerialize("ALTER VIEW view1 AS SELECT 1 WITH CHECK OPTION");
Assert::parseSerialize("ALTER VIEW view1 AS SELECT 1 WITH CASCADED CHECK OPTION");
Assert::parseSerialize("ALTER VIEW view1 AS SELECT 1 WITH LOCAL CHECK OPTION");


// CREATE [OR REPLACE] VIEW view_name [(column_list)] AS select_statement
Assert::parseSerialize("CREATE VIEW view1 AS SELECT 1");
Assert::parseSerialize("CREATE VIEW view1 (col1) AS SELECT 1");
Assert::parseSerialize("CREATE VIEW view1 (col1, col2) AS SELECT 1");
Assert::parseSerialize("CREATE OR REPLACE VIEW view1 AS SELECT 1");

// [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
Assert::parseSerialize("CREATE ALGORITHM = UNDEFINED VIEW view1 AS SELECT 1");
Assert::parseSerialize("CREATE ALGORITHM = MERGE VIEW view1 AS SELECT 1");
Assert::parseSerialize("CREATE ALGORITHM = TEMPTABLE VIEW view1 AS SELECT 1");

// [DEFINER = { user | CURRENT_USER }]
Assert::parseSerialize("CREATE DEFINER = usr1@host1 VIEW view1 AS SELECT 1");
Assert::parseSerialize("CREATE DEFINER = CURRENT_USER VIEW view1 AS SELECT 1");

// [SQL SECURITY { DEFINER | INVOKER }]
Assert::parseSerialize("CREATE SQL SECURITY DEFINER VIEW view1 AS SELECT 1");
Assert::parseSerialize("CREATE SQL SECURITY INVOKER VIEW view1 AS SELECT 1");

// [WITH [CASCADED | LOCAL] CHECK OPTION]
Assert::parseSerialize("CREATE VIEW view1 AS SELECT 1 WITH CHECK OPTION");
Assert::parseSerialize("CREATE VIEW view1 AS SELECT 1 WITH CASCADED CHECK OPTION");
Assert::parseSerialize("CREATE VIEW view1 AS SELECT 1 WITH LOCAL CHECK OPTION");


// DROP VIEW [IF EXISTS] view_name [, view_name] ... [RESTRICT | CASCADE]
Assert::parseSerialize("DROP VIEW view1");
Assert::parseSerialize("DROP VIEW IF EXISTS view1");
Assert::parseSerialize("DROP VIEW view1, view2");
Assert::parseSerialize("DROP VIEW view1 RESTRICT");
Assert::parseSerialize("DROP VIEW view1 CASCADE");
