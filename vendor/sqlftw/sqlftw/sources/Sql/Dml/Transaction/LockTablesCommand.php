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
use SqlFtw\Sql\Statement;

class LockTablesCommand extends Statement implements TransactionCommand
{

    /** @var non-empty-list<LockTablesItem> */
    private array $items;

    /**
     * @param non-empty-list<LockTablesItem> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return non-empty-list<LockTablesItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'LOCK TABLES ' . $formatter->formatSerializablesList($this->items);
    }

}
