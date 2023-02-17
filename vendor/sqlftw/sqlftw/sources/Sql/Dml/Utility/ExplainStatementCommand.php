<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Utility;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Command;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Statement;

class ExplainStatementCommand extends Statement implements DmlCommand
{

    private Command $statement;

    private ?ExplainType $type;

    public function __construct(Command $statement, ?ExplainType $type = null)
    {
        $this->statement = $statement;
        $this->type = $type;
    }

    public function getStatement(): Command
    {
        return $this->statement;
    }

    public function getType(): ?ExplainType
    {
        return $this->type;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'EXPLAIN ';
        if ($this->type !== null) {
            $result .= $this->type->serialize($formatter) . ' ';
        }
        $result .= $this->statement->serialize($formatter);

        return $result;
    }

}
