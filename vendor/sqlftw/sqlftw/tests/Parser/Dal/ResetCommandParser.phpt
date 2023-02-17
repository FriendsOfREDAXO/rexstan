<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// RESET reset_option [, reset_option] ...
Assert::parseSerialize("RESET MASTER");
Assert::parseSerialize("RESET SLAVE");
Assert::parseSerialize("RESET QUERY CACHE");
Assert::parseSerialize("RESET MASTER, SLAVE, QUERY CACHE");
