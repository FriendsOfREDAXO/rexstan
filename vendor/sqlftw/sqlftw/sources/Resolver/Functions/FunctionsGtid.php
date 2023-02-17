<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

trait FunctionsGtid
{

    // GTID_SUBSET() - Return true if all GTIDs in subset are also in set; otherwise false.
    // GTID_SUBTRACT() - Return all GTIDs in set that are not in subset.
    // WAIT_FOR_EXECUTED_GTID_SET() - Wait until the given GTIDs have executed on the replica.
    // WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS() - Use WAIT_FOR_EXECUTED_GTID_SET().

}
