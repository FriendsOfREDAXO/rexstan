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
use SqlFtw\Sql\Ddl\Table\Index\IndexDefinition;

class AddIndexAction implements IndexAction
{

    private IndexDefinition $index;

    public function __construct(IndexDefinition $index)
    {
        $this->index = $index;
    }

    public function getIndex(): IndexDefinition
    {
        return $this->index;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'ADD ' . $this->index->serialize($formatter);
    }

}
