<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// FLUSH
Assert::parseSerialize("FLUSH NO_WRITE_TO_BINLOG DES_KEY_FILE", "FLUSH LOCAL DES_KEY_FILE");
Assert::parseSerialize("FLUSH LOCAL HOSTS");
Assert::parseSerialize("FLUSH BINARY LOGS");
Assert::parseSerialize("FLUSH ENGINE LOGS");
Assert::parseSerialize("FLUSH ERROR LOGS");
Assert::parseSerialize("FLUSH GENERAL LOGS");
Assert::parseSerialize("FLUSH RELAY LOGS");
Assert::parseSerialize("FLUSH SLOW LOGS");
Assert::parseSerialize("FLUSH RELAY LOGS FOR CHANNEL chan1", "FLUSH RELAY LOGS FOR CHANNEL 'chan1'");
Assert::parseSerialize("FLUSH OPTIMIZER_COSTS");
Assert::parseSerialize("FLUSH PRIVILEGES");
Assert::parseSerialize("FLUSH QUERY CACHE");
Assert::parseSerialize("FLUSH STATUS");
Assert::parseSerialize("FLUSH USER_RESOURCES");
Assert::parseSerialize("FLUSH PRIVILEGES, STATUS");
