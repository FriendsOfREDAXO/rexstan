<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// IMPORT TABLE FROM sdi_file [, sdi_file] ...
Assert::parseSerialize("IMPORT TABLE FROM 'file1'");
Assert::parseSerialize("IMPORT TABLE FROM 'file1', 'file2'");
