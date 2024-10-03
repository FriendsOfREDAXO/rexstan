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
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\StatementImpl;
use function count;

class CaseStatement extends StatementImpl
{

    private ?RootNode $condition;

    /** @var non-empty-list<RootNode> */
    private array $values;

    /** @var non-empty-list<list<Statement>> */
    private array $statementLists;

    /**
     * @param non-empty-list<RootNode> $values
     * @param non-empty-list<list<Statement>> $statementLists
     */
    public function __construct(?RootNode $condition, array $values, array $statementLists)
    {
        if (count($statementLists) < count($values) || count($statementLists) > count($values) + 1) {
            throw new InvalidDefinitionException('Count of statement lists should be same or one higher then count of values.');
        }

        $this->condition = $condition;
        $this->values = $values;
        $this->statementLists = $statementLists;
    }

    public function getCondition(): ?RootNode
    {
        return $this->condition;
    }

    /**
     * @return non-empty-list<RootNode>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return non-empty-list<list<Statement>>
     */
    public function getStatementLists(): array
    {
        return $this->statementLists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CASE ';
        if ($this->condition !== null) {
            $result .= $this->condition->serialize($formatter) . "\n";
        }
        foreach ($this->values as $i => $condition) {
            $result .= 'WHEN ' . $this->values[$i]->serialize($formatter) . " THEN \n";
            $statements = $this->statementLists[$i];
            if ($statements !== []) {
                $result .= $formatter->formatSerializablesList($statements, ";\n") . ";\n";
            }
        }
        if (count($this->values) < count($this->statementLists)) {
            $result .= "ELSE\n";
            $statements = $this->statementLists[count($this->values)];
            if ($statements !== []) {
                $result .= $formatter->formatSerializablesList($statements, ";\n") . ";\n";
            }
        }

        return $result . 'END CASE';
    }

}
