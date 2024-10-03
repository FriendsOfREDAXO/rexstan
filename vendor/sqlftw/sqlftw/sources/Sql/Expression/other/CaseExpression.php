<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\StatementImpl;
use function count;

/**
 * CASE x THEN y ELSE z END
 */
class CaseExpression extends StatementImpl implements RootNode
{

    private ?RootNode $condition;

    /** @var non-empty-list<RootNode> */
    private array $values;

    /** @var non-empty-list<RootNode> */
    private array $results;

    /**
     * @param non-empty-list<RootNode> $values
     * @param non-empty-list<RootNode> $results
     */
    public function __construct(?RootNode $condition, array $values, array $results)
    {
        if (count($results) < count($values) || count($results) > count($values) + 1) {
            throw new InvalidDefinitionException('Count of results should be same or one higher then count of values.');
        }

        $this->condition = $condition;
        $this->values = $values;
        $this->results = $results;
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
     * @return non-empty-list<RootNode>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CASE';
        if ($this->condition !== null) {
            $result .= ' ' . $this->condition->serialize($formatter);
        }
        foreach ($this->values as $i => $condition) {
            $result .= ' WHEN ' . $this->values[$i]->serialize($formatter) . ' THEN ' . $this->results[$i]->serialize($formatter);
        }
        if (count($this->values) < count($this->results)) {
            $result .= ' ELSE ' . $this->results[count($this->values)]->serialize($formatter);
        }
        $result .= ' END';

        return $result;
    }

}
