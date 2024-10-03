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
use SqlFtw\Sql\StatementImpl;

class StartTransactionCommand extends StatementImpl implements TransactionCommand
{

    private ?bool $consistent;

    private ?bool $write;

    public function __construct(?bool $consistent = null, ?bool $write = null)
    {
        $this->consistent = $consistent;
        $this->write = $write;
    }

    public function getConsistent(): ?bool
    {
        return $this->consistent;
    }

    public function getWrite(): ?bool
    {
        return $this->write;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'START TRANSACTION';
        if ($this->consistent !== null) {
            $result .= ' WITH CONSISTENT SNAPSHOT';
        }
        if ($this->consistent !== null && $this->write !== null) {
            $result .= ',';
        }
        if ($this->write !== null) {
            $result .= $this->write ? ' READ WRITE' : ' READ ONLY';
        }

        return $result;
    }

}
