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

class DropForeignKeyAction implements ForeignKeyAction
{

    private string $foreignKey;

    public function __construct(string $foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DROP FOREIGN KEY ' . $formatter->formatName($this->foreignKey);
    }

}
