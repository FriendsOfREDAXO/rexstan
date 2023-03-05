<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Statement;

class ShowProcessListCommand extends Statement implements ShowCommand
{

    private bool $full;

    public function __construct(bool $full = false)
    {
        $this->full = $full;
    }

    public function isFull(): bool
    {
        return $this->full;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SHOW' . ($this->full ? ' FULL' : '') . ' PROCESSLIST';
    }

}
