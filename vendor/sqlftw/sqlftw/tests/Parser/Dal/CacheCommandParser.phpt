<?php declare(strict_types = 1);

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';


// CACHE INDEX
Assert::parseSerialize("CACHE INDEX table1 INDEX (index1) PARTITION (ALL) IN keyCache1");
Assert::parseSerialize(
    "CACHE INDEX table1 INDEX (index1, index2), table2 KEY (index3) PARTITION (partition1, partition2) IN keyCache1",
    "CACHE INDEX table1 INDEX (index1, index2), table2 INDEX (index3) PARTITION (partition1, partition2) IN keyCache1"
);


// LOAD INDEX INTO CACHE
Assert::parseSerialize(
    "LOAD INDEX INTO CACHE table1 PARTITION (partition1, partition2) INDEX (index1, index2), table2 PARTITION (ALL) KEY (index3) IGNORE LEAVES",
    "LOAD INDEX INTO CACHE table1 PARTITION (partition1, partition2) INDEX (index1, index2), table2 PARTITION (ALL) INDEX (index3) IGNORE LEAVES"
);
