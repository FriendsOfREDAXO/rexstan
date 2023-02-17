<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Transaction;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\SqlSerializable;

class LockTablesItem implements SqlSerializable
{

    private ObjectIdentifier $table;

    private LockTableType $lock;

    private ?string $alias;

    public function __construct(ObjectIdentifier $table, LockTableType $lock, ?string $alias)
    {
        $this->table = $table;
        $this->lock = $lock;
        $this->alias = $alias;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function getLock(): LockTableType
    {
        return $this->lock;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->table->serialize($formatter);
        if ($this->alias !== null) {
            $result .= ' AS ' . $formatter->formatName($this->alias);
        }
        $result .= ' ' . $this->lock->serialize($formatter);

        return $result;
    }

}
