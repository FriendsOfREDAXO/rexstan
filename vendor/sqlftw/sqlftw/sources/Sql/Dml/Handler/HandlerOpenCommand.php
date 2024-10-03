<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Handler;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class HandlerOpenCommand extends StatementImpl implements HandlerCommand
{

    private ObjectIdentifier $table;

    private ?string $alias;

    public function __construct(ObjectIdentifier $table, ?string $alias = null)
    {
        $this->table = $table;
        $this->alias = $alias;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'HANDLER ' . $this->table->serialize($formatter) . ' OPEN';
        if ($this->alias !== null) {
            $result .= ' AS ' . $formatter->formatName($this->alias);
        }

        return $result;
    }

}
