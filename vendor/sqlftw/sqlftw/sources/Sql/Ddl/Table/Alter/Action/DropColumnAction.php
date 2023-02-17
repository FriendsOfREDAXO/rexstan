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

class DropColumnAction implements ColumnAction
{

    private string $column;

    public function __construct(string $column)
    {
        $this->column = $column;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DROP COLUMN ' . $formatter->formatName($this->column);
    }

}
