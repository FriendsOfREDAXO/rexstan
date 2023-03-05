<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// SET CHARSET
Assert::parseSerialize("SET CHARACTER SET utf8");
Assert::parseSerialize("SET CHARACTER SET 'utf8'", "SET CHARACTER SET utf8");
Assert::parseSerialize("SET CHARSET DEFAULT", "SET CHARACTER SET DEFAULT");


// SET NAMES
Assert::parseSerialize("SET NAMES utf8 COLLATE utf8_unicode_ci");
Assert::parseSerialize("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'", "SET NAMES utf8 COLLATE utf8_unicode_ci");
Assert::parseSerialize("SET NAMES DEFAULT");
