<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\TableReference;

use Countable;
use SqlFtw\Formatter\Formatter;

class TableReferenceParentheses implements TableReferenceNode, Countable
{

    private TableReferenceNode $content;

    public function __construct(TableReferenceNode $content)
    {
        $this->content = $content;
    }

    public function count(): int
    {
        return $this->content instanceof Countable ? $this->content->count() : 1;
    }

    public function getContent(): TableReferenceNode
    {
        return $this->content;
    }

    public function serialize(Formatter $formatter): string
    {
        return '(' . $this->content->serialize($formatter) . ')';
    }

}
