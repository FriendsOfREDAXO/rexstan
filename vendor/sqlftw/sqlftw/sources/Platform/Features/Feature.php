<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform\Features;

class Feature
{

    public const OPTIMIZER_HINTS = 'optimizer-hints'; // /*+ ... */
    public const REQUIRE_TABLE_PRIMARY_KEY_CHECK_GENERATE = 'require-table-primary-key-check-generate'; // >=8.0.32

    // deprecation of old features
    public const OLD_NULL_LITERAL = 'old-null-literal'; // \N
    public const UNQUOTED_NAMES_CAN_START_WITH_DOLLAR_SIGN = 'unquoted-names-can-start-with-dollar-sign'; // depr. 8.0.32
    public const FULL_IS_VALID_NAME = 'full-is-valid-name'; // depr. 8.0.32

}
