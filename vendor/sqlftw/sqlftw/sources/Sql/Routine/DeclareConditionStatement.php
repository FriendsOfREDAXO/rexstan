<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\Error\SqlState;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;

class DeclareConditionStatement extends Statement implements SqlSerializable
{

    private string $condition;

    /** @var int|SqlState */
    private $value;

    /**
     * @param int|SqlState $value
     */
    public function __construct(string $condition, $value)
    {
        $this->condition = $condition;
        $this->value = $value;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * @return int|SqlState
     */
    public function getValue()
    {
        return $this->value;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DECLARE ' . $formatter->formatName($this->condition) . ' CONDITION FOR ';
        if ($this->value instanceof SqlState) {
            $result .= 'SQLSTATE ' . $this->value->serialize($formatter);
        } else {
            $result .= $this->value;
        }

        return $result;
    }

}
