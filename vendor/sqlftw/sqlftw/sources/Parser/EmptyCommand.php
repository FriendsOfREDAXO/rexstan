<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\Statement;

/**
 * Returned when parsing empty string
 */
class EmptyCommand extends Statement implements Command
{

    /**
     * @param list<string> $commentsBefore
     */
    public function __construct(array $commentsBefore)
    {
        $this->commentsBefore = $commentsBefore;
    }

    public function serialize(Formatter $formatter): string
    {
        return '';
    }

}
