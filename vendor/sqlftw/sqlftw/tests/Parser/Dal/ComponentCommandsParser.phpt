<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// INSTALL COMPONENT
Assert::parseSerialize("INSTALL COMPONENT com1, com2");


// UNINSTALL COMPONENT
Assert::parseSerialize("UNINSTALL COMPONENT com1, com2");
