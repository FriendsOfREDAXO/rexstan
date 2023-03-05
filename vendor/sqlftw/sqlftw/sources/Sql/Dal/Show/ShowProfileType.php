<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;

class ShowProfileType extends SqlEnum
{

    public const ALL = Keyword::ALL;
    public const BLOCK_IO = Keyword::BLOCK . ' ' . Keyword::IO;
    public const CONTEXT_SWITCHES = Keyword::CONTEXT . ' ' . Keyword::SWITCHES;
    public const CPU = Keyword::CPU;
    public const IPC = Keyword::IPC;
    public const MEMORY = Keyword::MEMORY;
    public const PAGE_FAULTS = Keyword::PAGE . ' ' . Keyword::FAULTS;
    public const SOURCE = Keyword::SOURCE;
    public const SWAPS = Keyword::SWAPS;

}
