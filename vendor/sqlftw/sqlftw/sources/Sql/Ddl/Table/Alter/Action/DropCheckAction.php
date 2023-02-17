<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Alter\Action;

use SqlFtw\Formatter\Formatter;

class DropCheckAction implements CheckAction
{

    private string $check;

    public function __construct(string $check)
    {
        $this->check = $check;
    }

    public function getCheck(): string
    {
        return $this->check;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DROP CHECK ' . $formatter->formatName($this->check);
    }

}
