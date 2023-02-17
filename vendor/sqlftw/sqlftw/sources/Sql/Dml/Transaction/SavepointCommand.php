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

class SavepointCommand extends Statement implements TransactionCommand
{

    private string $savepoint;

    public function __construct(string $savepoint)
    {
        $this->savepoint = $savepoint;
    }

    public function getSavepoint(): string
    {
        return $this->savepoint;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SAVEPOINT ' . $formatter->formatName($this->savepoint);
    }

}
