<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Prepared;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\UserVariable;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;

class PrepareCommand extends Statement implements PreparedStatementCommand
{

    private string $name;

    /** @var UserVariable|Statement */
    private SqlSerializable $statement;

    /**
     * @param UserVariable|Statement $statement
     */
    public function __construct(string $name, $statement)
    {
        $this->name = $name;
        $this->statement = $statement;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return UserVariable|Statement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    public function serialize(Formatter $formatter): string
    {
        $statement = $this->statement->serialize($formatter);

        return 'PREPARE ' . $formatter->formatName($this->name) . ' FROM '
            . ($this->statement instanceof UserVariable ? $statement : $formatter->formatString($statement))
            . ($this->statement instanceof Statement ? $this->statement->getDelimiter() : '');
    }

}
