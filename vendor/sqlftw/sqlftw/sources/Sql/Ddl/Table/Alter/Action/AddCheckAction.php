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
use SqlFtw\Sql\Ddl\Table\Constraint\CheckDefinition;

class AddCheckAction implements CheckAction
{

    private CheckDefinition $check;

    public function __construct(CheckDefinition $check)
    {
        $this->check = $check;
    }

    public function getCheck(): CheckDefinition
    {
        return $this->check;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'ADD ' . $this->check->serialize($formatter);
    }

}
