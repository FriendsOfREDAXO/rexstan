<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// DELIMITER ??
Assert::parseSerialize("DELIMITER ;;");
