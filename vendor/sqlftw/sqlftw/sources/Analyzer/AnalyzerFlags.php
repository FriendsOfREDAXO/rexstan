<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer;

final class AnalyzerFlags
{
    public const NONE = 0;
    public const REPAIR = 1;
    public const GENERATE_REPAIR_COMMAND = 2;
    public const GENERATE_REVERSE_COMMAND = 4;

}
