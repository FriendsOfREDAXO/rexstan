<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// RESET PERSIST [[IF EXISTS] system_var_name]
Assert::parseSerialize("RESET PERSIST");
Assert::parseSerialize("RESET PERSIST sql_mode");
Assert::parseSerialize("RESET PERSIST IF EXISTS sql_mode");
