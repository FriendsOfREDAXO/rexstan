<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// KILL
Assert::parseSerialize("KILL 17");
Assert::parseSerialize("KILL CONNECTION 17", "KILL 17");
Assert::parseSerialize("KILL QUERY 17", "KILL 17");
