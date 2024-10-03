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
use SqlFtw\Sql\Dml\OptimizerHint\OptimizerHint;
use SqlFtw\Sql\Dml\TableReference\TableReferenceNode;
use SqlFtw\Sql\Dml\WithClause;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Expression\OrderByExpression;
use SqlFtw\Sql\Expression\Placeholder;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\StatementImpl;

class SelectCommand extends StatementImpl implements SimpleQuery
{

    /** @var non-empty-list<SelectExpression> */
    private array $columns;

    private ?TableReferenceNode $from;

    private ?ExpressionNode $where;

    /** @var non-empty-list<GroupByExpression>|null */
    private ?array $groupBy;

    private ?ExpressionNode $having;

    private ?WithClause $with;

    /** @var non-empty-array<string, WindowSpecification>|null */
    private ?array $windows;

    /** @var non-empty-list<OrderByExpression>|null */
    private ?array $orderBy;

    /** @var int|SimpleName|Placeholder|null */
    private $limit;

    /** @var int|SimpleName|Placeholder|null */
    private $offset;

    private ?SelectDistinctOption $distinct;

    /** @var array<SelectOption::*, bool> */
    private array $options;

    private ?SelectInto $into;

    /** @var non-empty-list<SelectLocking>|null */
    private ?array $locking;

    private bool $withRollup;

    /** @var non-empty-list<OptimizerHint>|null */
    private ?array $optimizerHints;

    /**
     * @param non-empty-list<SelectExpression> $columns
     * @param non-empty-list<GroupByExpression>|null $groupBy
     * @param non-empty-array<string, WindowSpecification>|null $windows ($name => $spec)
     * @param non-empty-list<OrderByExpression>|null $orderBy
     * @param int|SimpleName|Placeholder|null $limit
     * @param int|SimpleName|Placeholder|null $offset
     * @param array<SelectOption::*, bool> $options
     * @param non-empty-list<SelectLocking>|null $locking
     * @param non-empty-list<OptimizerHint>|null $optimizerHints
     */
    public function __construct(
        array $columns,
        ?TableReferenceNode $from,
        ?ExpressionNode $where = null,
        ?array $groupBy = null,
        ?ExpressionNode $having = null,
        ?WithClause $with = null,
        ?array $windows = null,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
        ?SelectDistinctOption $distinct = null,
        array $options = [],
        ?SelectInto $into = null,
        ?array $locking = null,
        bool $withRollup = false,
        ?array $optimizerHints = null
    ) {
        if ($groupBy === null && $withRollup === true) {
            throw new InvalidDefinitionException('WITH ROLLUP can be used only with GROUP BY.');
        }
        foreach ($options as $option => $value) {
            SelectOption::checkValue($option);
        }

        $this->columns = $columns;
        $this->from = $from;
        $this->where = $where;
        $this->groupBy = $groupBy;
        $this->having = $having;
        $this->with = $with;
        $this->windows = $windows;
        $this->orderBy = $orderBy;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->distinct = $distinct;
        $this->options = $options;
        $this->into = $into;
        $this->locking = $locking;
        $this->withRollup = $withRollup;
        $this->optimizerHints = $optimizerHints;
    }

    /**
     * @return non-empty-list<SelectExpression>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getFrom(): ?TableReferenceNode
    {
        return $this->from;
    }

    public function getWhere(): ?ExpressionNode
    {
        return $this->where;
    }

    /**
     * @return non-empty-list<GroupByExpression>|null
     */
    public function getGroupBy(): ?array
    {
        return $this->groupBy;
    }

    public function withRollup(): bool
    {
        return $this->withRollup;
    }

    public function getHaving(): ?ExpressionNode
    {
        return $this->having;
    }

    public function getWith(): ?WithClause
    {
        return $this->with;
    }

    /**
     * @return non-empty-array<string, WindowSpecification>|null
     */
    public function getWindows(): ?array
    {
        return $this->windows;
    }

    /**
     * @return non-empty-list<OrderByExpression>|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

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

    public function getDistinct(): ?SelectDistinctOption
    {
        return $this->distinct;
    }

    /**
     * @return array<SelectOption::*, bool>
     */
    public function getOptions(): array
    {
        return $this->options;
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

    public function removeLocking(): self
    {
        $that = clone $this;
        $that->locking = null;

        return $that;
    }

    /**
     * @return non-empty-list<OptimizerHint>|null
     */
    public function getOptimizerHints(): ?array
    {
        return $this->optimizerHints;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        if ($this->with !== null) {
            $result .= $this->with->serialize($formatter) . "\n";
        }

        $result .= 'SELECT';

        if ($this->optimizerHints !== null) {
            $result .= ' /*+ ' . $formatter->formatSerializablesList($this->optimizerHints) . ' */';
        }

        if ($this->distinct !== null) {
            $result .= ' ' . $this->distinct->serialize($formatter);
        }
        foreach ($this->options as $option => $value) {
            if ($value) {
                $result .= ' ' . $option;
            }
        }

        $result .= ' ' . $formatter->formatSerializablesList($this->columns);

        if ($this->into !== null && $this->into->getPosition() === SelectInto::POSITION_BEFORE_FROM) {
            $result .= "\n" . $this->into->serialize($formatter);
        }
        if ($this->from !== null) {
            $result .= "\nFROM " . $this->from->serialize($formatter);
        }
        if ($this->where !== null) {
            $result .= "\nWHERE " . $this->where->serialize($formatter);
        }
        if ($this->groupBy !== null) {
            $result .= "\nGROUP BY " . $formatter->formatSerializablesList($this->groupBy, ",\n\t");
            if ($this->withRollup) {
                $result .= "\n\tWITH ROLLUP";
            }
        }
        if ($this->having !== null) {
            $result .= "\nHAVING " . $this->having->serialize($formatter);
        }
        if ($this->windows !== null) {
            $result .= "\nWINDOW ";
            $first = true;
            foreach ($this->windows as $name => $window) {
                if (!$first) {
                    $result .= "\n\t";
                }
                $result .= $formatter->formatName($name) . ' AS ' . $window->serialize($formatter);
                $first = false;
            }
        }
        if ($this->orderBy !== null) {
            $result .= "\nORDER BY " . $formatter->formatSerializablesList($this->orderBy, ",\n\t");
        }
        if ($this->limit !== null) {
            $result .= "\nLIMIT " . ($this->limit instanceof SqlSerializable ? $this->limit->serialize($formatter) : $this->limit);
            if ($this->offset !== null) {
                $result .= "\nOFFSET " . ($this->offset instanceof SqlSerializable ? $this->offset->serialize($formatter) : $this->offset);
            }
        }
        if ($this->into !== null && $this->into->getPosition() === SelectInto::POSITION_BEFORE_LOCKING) {
            $result .= "\n" . $this->into->serialize($formatter);
        }
        if ($this->locking !== null) {
            foreach ($this->locking as $locking) {
                $result .= "\n" . $locking->serialize($formatter);
            }
        }
        if ($this->into !== null && $this->into->getPosition() === SelectInto::POSITION_AFTER_LOCKING) {
            $result .= "\n" . $this->into->serialize($formatter);
        }

        return $result;
    }

}
