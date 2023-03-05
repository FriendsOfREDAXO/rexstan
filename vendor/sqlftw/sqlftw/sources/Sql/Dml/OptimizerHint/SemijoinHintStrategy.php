<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\OptimizerHint;

use SqlFtw\Sql\SqlEnum;

class SemijoinHintStrategy extends SqlEnum
{

    public const DUPLICATES_WEED_OUT = 'DUPSWEEDOUT';
    public const FIRST_MATCH = 'FIRSTMATCH';
    public const LOOSE_SCAN = 'LOOSESCAN';
    public const MATERIALIZATION = 'MATERIALIZATION';

}
