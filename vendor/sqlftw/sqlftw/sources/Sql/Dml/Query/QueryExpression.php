<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\OrderByExpression;
use SqlFtw\Sql\Expression\Placeholder;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\StatementImpl;
use function array_values;
use function count;

class QueryExpression extends StatementImpl implements Query
{

    /** @var non-empty-list<Query> */
    private array $queries;

    /** @var non-empty-list<QueryOperator> */
    private array $operators;

    /** @var non-empty-list<OrderByExpression>|null */
    private ?array $orderBy;

    /** @var int|SimpleName|Placeholder|null */
    private $limit;

    /** @var int|SimpleName|Placeholder|null */
    private $offset;

    private ?SelectInto $into;

    /** @var non-empty-list<SelectLocking>|null */
    private ?array $locking;

    /**
     * @param non-empty-list<Query> $queries
     * @param non-empty-list<QueryOperator> $operators
     * @param non-empty-list<OrderByExpression>|null $orderBy
     * @param int|SimpleName|Placeholder|null $limit
     * @param int|SimpleName|Placeholder|null $offset
     * @param non-empty-list<SelectLocking>|null $locking
     */
    public function __construct(
        array $queries,
        array $operators,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
        ?SelectInto $into = null,
        ?array $locking = null
    )
    {
        if (count($queries) !== count($operators) + 1) {
            throw new InvalidDefinitionException('Count of queries must be exactly 1 higher then count of query operators.');
        }
        $this->queries = array_values($queries);
        $this->operators = array_values($operators);
        $this->orderBy = $orderBy;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->into = $into;
        $this->locking = $locking;
    }

    /**
     * @return non-empty-list<Query>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @return non-empty-list<QueryOperator>
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * @return non-empty-list<OrderByExpression>|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    /**
     * @return static
     */
    public function removeOrderBy(): Query
    {
        $that = clone $this;
        $that->orderBy = null;

        return $that;
    }

    /**
     * @return int|SimpleName|Placeholder|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return static
     */
    public function removeLimit(): Query
    {
        $that = clone $this;
        $that->limit = null;

        return $that;
    }

    /**
     * @return int|SimpleName|Placeholder|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return static
     */
    public function removeOffset(): Query
    {
        $that = clone $this;
        $that->offset = null;

        return $that;
    }

    public function getInto(): ?SelectInto
    {
        return $this->into;
    }

    public function removeInto(): Query
    {
        $that = clone $this;
        $that->into = null;

        return $that;
    }

    /**
     * @return non-empty-list<SelectLocking>|null
     */
    public function getLocking(): ?array
    {
        return $this->locking;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->queries[0]->serialize($formatter);

        foreach ($this->operators as $i => $operator) {
            $result .= "\n\t" . $operator->serialize($formatter) . "\n" . $this->queries[$i + 1]->serialize($formatter);
        }

        if ($this->orderBy !== null) {
            $result .= "\n\tORDER BY " . $formatter->formatSerializablesList($this->orderBy, ",\n\t");
        }
        if ($this->limit !== null) {
            $result .= "\n\tLIMIT " . ($this->limit instanceof SqlSerializable ? $this->limit->serialize($formatter) : $this->limit);
            if ($this->offset !== null) {
                $result .= "\nOFFSET " . ($this->offset instanceof SqlSerializable ? $this->offset->serialize($formatter) : $this->offset);
            }
        }
        if ($this->locking !== null) {
            $result .= "\n\t" . $formatter->formatSerializablesList($this->locking);
        }
        if ($this->into !== null) {
            $result .= "\n\t" . $formatter->indent($this->into->serialize($formatter));
        }

        return $result;
    }

}
