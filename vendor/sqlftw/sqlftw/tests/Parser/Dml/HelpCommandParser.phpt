<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// HELP 'search_string'
Assert::parseSerialize("HELP 'search1'");
