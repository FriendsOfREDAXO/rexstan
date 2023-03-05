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
use SqlFtw\Sql\Ddl\Table\Constraint\ForeignKeyDefinition;

class AddForeignKeyAction implements ForeignKeyAction
{

    private ForeignKeyDefinition $foreignKey;

    public function __construct(ForeignKeyDefinition $foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }

    public function getForeignKey(): ForeignKeyDefinition
    {
        return $this->foreignKey;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'ADD ' . $this->foreignKey->serialize($formatter);
    }

}
