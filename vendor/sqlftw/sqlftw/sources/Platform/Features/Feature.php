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
    public const OLD_NULL_LITERAL = 'old-null-literal'; // \N

}
