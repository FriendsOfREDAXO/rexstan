<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// INSTALL PLUGIN
Assert::parseSerialize("INSTALL PLUGIN plug1 SONAME 'library.so'");

// UNINSTALL PLUGIN
Assert::parseSerialize("UNINSTALL PLUGIN plug1");
