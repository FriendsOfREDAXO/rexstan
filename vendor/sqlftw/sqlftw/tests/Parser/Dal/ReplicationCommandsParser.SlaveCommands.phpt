<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// RESET MASTER
Assert::parseSerialize("RESET MASTER");
Assert::parseSerialize("RESET MASTER TO 12345");


// RESET SLAVE
Assert::parseSerialize("RESET SLAVE");
Assert::parseSerialize("RESET SLAVE ALL");
Assert::parseSerialize("RESET SLAVE FOR CHANNEL 'chan1'");
Assert::parseSerialize("RESET SLAVE ALL FOR CHANNEL 'chan1'");


// START GROUP_REPLICATION
Assert::parseSerialize("START GROUP_REPLICATION");


// START SLAVE
Assert::parseSerialize("START SLAVE");
Assert::parseSerialize("START SLAVE IO_THREAD");
Assert::parseSerialize("START SLAVE SQL_THREAD");
Assert::parseSerialize("START SLAVE IO_THREAD, SQL_THREAD");
Assert::parseSerialize("START SLAVE UNTIL SQL_BEFORE_GTIDS = 5ade17eb-fb52-49e5-80c9-6b952de466b7:10");
Assert::parseSerialize("START SLAVE UNTIL SQL_AFTER_GTIDS = 5ade17eb-fb52-49e5-80c9-6b952de466b7:10");
Assert::parseSerialize("START SLAVE UNTIL SQL_AFTER_GTIDS = 5ade17eb-fb52-49e5-80c9-6b952de466b7:10-20:30-40:50-60");
Assert::parseSerialize("START SLAVE UNTIL SQL_AFTER_GTIDS = 5ade17eb-fb52-49e5-80c9-6b952de466b7:10-20:30-40:50-60, 82c4b49c-b591-4249-8600-d2ba6a528791:70-80");
Assert::parseSerialize("START SLAVE USER='usr1' PASSWORD='pwd1' DEFAULT_AUTH='auth1' PLUGIN_DIR='dir1'");
Assert::parseSerialize("START SLAVE FOR CHANNEL 'chan1'");


// STOP GROUP_REPLICATION
Assert::parseSerialize("STOP GROUP_REPLICATION");


// STOP SLAVE
Assert::parseSerialize("STOP SLAVE");
Assert::parseSerialize("STOP SLAVE IO_THREAD");
Assert::parseSerialize("STOP SLAVE SQL_THREAD");
Assert::parseSerialize("STOP SLAVE IO_THREAD, SQL_THREAD");
Assert::parseSerialize("STOP SLAVE FOR CHANNEL 'chan1'");
