<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class CreateTableLikeCommand extends StatementImpl implements AnyCreateTableCommand
{

    private ObjectIdentifier $name;

    private ObjectIdentifier $templateTable;

    private bool $temporary;

    private bool $ifNotExists;

    public function __construct(
        ObjectIdentifier $name,
        ObjectIdentifier $templateTable,
        bool $temporary = false,
        bool $ifNotExists = false
    ) {
        $this->name = $name;
        $this->templateTable = $templateTable;
        $this->temporary = $temporary;
        $this->ifNotExists = $ifNotExists;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->name;
    }

    public function getTemplateTable(): ObjectIdentifier
    {
        return $this->templateTable;
    }

    public function isTemporary(): bool
    {
        return $this->temporary;
    }

    public function ifNotExists(): bool
    {
        return $this->ifNotExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CREATE ';
        if ($this->temporary) {
            $result .= 'TEMPORARY ';
        }
        $result .= 'TABLE ';
        if ($this->ifNotExists) {
            $result .= 'IF NOT EXISTS ';
        }
        $result .= $this->name->serialize($formatter);

        $result .= ' LIKE ' . $this->templateTable->serialize($formatter);

        return $result;
    }

}
