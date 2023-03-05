<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CALL sp_name[([parameter[, ...]])]
Assert::parseSerialize("CALL proc1");
Assert::parseSerialize("CALL proc1(var1)");
Assert::parseSerialize("CALL proc1(var1, var2)");
