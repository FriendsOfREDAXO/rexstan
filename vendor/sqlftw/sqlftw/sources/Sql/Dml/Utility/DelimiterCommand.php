<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Utility;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\StatementImpl;

class DelimiterCommand extends StatementImpl implements DmlCommand
{

    private string $newDelimiter;

    public function __construct(string $newDelimiter)
    {
        $this->newDelimiter = $newDelimiter;
    }

    public function getNewDelimiter(): string
    {
        return $this->newDelimiter;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DELIMITER ' . $this->newDelimiter;
    }

}
