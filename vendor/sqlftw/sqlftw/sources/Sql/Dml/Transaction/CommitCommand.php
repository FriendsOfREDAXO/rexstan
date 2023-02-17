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
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Statement;

class CommitCommand extends Statement implements TransactionCommand
{

    private ?bool $chain;

    private ?bool $release;

    public function __construct(?bool $chain, ?bool $release)
    {
        if ($chain === true && $release === true) {
            throw new InvalidDefinitionException('CHAIN and RELEASE cannot be both specified.');
        }

        $this->chain = $chain;
        $this->release = $release;
    }

    public function chain(): ?bool
    {
        return $this->chain;
    }

    public function release(): ?bool
    {
        return $this->release;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'COMMIT';
        if ($this->chain !== null) {
            $result .= $this->chain ? ' AND CHAIN' : ' AND NO CHAIN';
        }
        if ($this->release !== null) {
            $result .= $this->release ? ' RELEASE' : ' NO RELEASE';
        }

        return $result;
    }

}
