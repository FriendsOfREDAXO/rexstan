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
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Statement;

class ShowIndexesCommand extends Statement implements ShowCommand
{

    private ObjectIdentifier $table;

    private ?RootNode $where;

    private bool $extended;

    public function __construct(ObjectIdentifier $table, ?RootNode $where = null, bool $extended = false)
    {
        $this->table = $table;
        $this->where = $where;
        $this->extended = $extended;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function getWhere(): ?RootNode
    {
        return $this->where;
    }

    public function extended(): bool
    {
        return $this->extended;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SHOW ';
        if ($this->extended) {
            $result .= 'EXTENDED ';
        }

        $result .= 'INDEXES FROM ' . $this->table->serialize($formatter);
        if ($this->where !== null) {
            $result .= ' WHERE ' . $this->where->serialize($formatter);
        }

        return $result;
    }

}
