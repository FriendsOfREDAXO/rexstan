<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

// CHANGE REPLICATION FILTER
Assert::parseSerialize("CHANGE REPLICATION FILTER
  REPLICATE_DO_DB = (db1, db2),
  REPLICATE_IGNORE_DB = (db3, db4),
  REPLICATE_DO_TABLE = (db1.table1, db2.table2),
  REPLICATE_IGNORE_TABLE = (db3.table3, db4.table4),
  REPLICATE_WILD_DO_TABLE = ('db5*.table5*', 'db6*.table6*'),
  REPLICATE_WILD_IGNORE_TABLE = ('db7*.table7*', 'db8*.table8*'),
  REPLICATE_REWRITE_DB = ((db1, db2), (db3, db4))");
