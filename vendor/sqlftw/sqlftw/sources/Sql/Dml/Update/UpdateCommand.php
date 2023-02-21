<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Update;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Assignment;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Dml\OptimizerHint\OptimizerHint;
use SqlFtw\Sql\Dml\TableReference\TableReferenceList;
use SqlFtw\Sql\Dml\TableReference\TableReferenceNode;
use SqlFtw\Sql\Dml\WithClause;
use SqlFtw\Sql\Expression\OrderByExpression;
use SqlFtw\Sql\Expression\Placeholder;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;
use function count;

class UpdateCommand extends Statement implements DmlCommand
{

    private TableReferenceNode $tableReferences;

    /** @var non-empty-list<Assignment> */
    private array $values;

    private ?RootNode $where;

    private ?WithClause $with;

    /** @var non-empty-list<OrderByExpression>|null */
    private ?array $orderBy;

    /** @var int|SimpleName|Placeholder|null */
    private $limit;

    private bool $ignore;

    private bool $lowPriority;

    /** @var non-empty-list<OptimizerHint>|null */
    private ?array $optimizerHints;

    /**
     * @param non-empty-list<Assignment> $values
     * @param non-empty-list<OrderByExpression>|null $orderBy
     * @param int|SimpleName|Placeholder|null $limit
     * @param non-empty-list<OptimizerHint>|null $optimizerHints
     */
    public function __construct(
        TableReferenceNode $tableReferences,
        array $values,
        ?RootNode $where = null,
        ?WithClause $with = null,
        ?array $orderBy = null,
        $limit = null,
        bool $ignore = false,
        bool $lowPriority = false,
        ?array $optimizerHints = null
    ) {
        if ($tableReferences instanceof TableReferenceList && count($tableReferences) > 1 && ($orderBy !== null || $limit !== null)) {
            throw new InvalidDefinitionException('ORDER BY and LIMIT must not be set, when more table references are used.');
        }

        $this->tableReferences = $tableReferences;
        $this->values = $values;
        $this->where = $where;
        $this->with = $with;
        $this->orderBy = $orderBy;
        $this->limit = $limit;
        $this->ignore = $ignore;
        $this->lowPriority = $lowPriority;
        $this->optimizerHints = $optimizerHints;
    }

    public function getTableReferences(): TableReferenceNode
    {
        return $this->tableReferences;
    }

    /**
     * @return non-empty-list<Assignment>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getWhere(): ?RootNode
    {
        return $this->where;
    }

    public function getWith(): ?WithClause
    {
        return $this->with;
    }

    /**
     * @return non-empty-list<OrderByExpression>|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    /**
     * @return int|SimpleName|Placeholder|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    public function ignore(): bool
    {
        return $this->ignore;
    }

    public function lowPriority(): bool
    {
        return $this->lowPriority;
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

        $result .= 'UPDATE ';

        if ($this->optimizerHints !== null) {
            $result .= '/*+ ' . $formatter->formatSerializablesList($this->optimizerHints) . ' */ ';
        }

        if ($this->lowPriority) {
            $result .= 'LOW_PRIORITY ';
        }
        if ($this->ignore) {
            $result .= 'IGNORE ';
        }

        $result .= $this->tableReferences->serialize($formatter);
        $result .= ' SET ' . $formatter->formatSerializablesList($this->values);

        if ($this->where !== null) {
            $result .= ' WHERE ' . $this->where->serialize($formatter);
        }
        if ($this->orderBy !== null) {
            $result .= ' ORDER BY ' . $formatter->formatSerializablesList($this->orderBy);
        }
        if ($this->limit !== null) {
            $result .= ' LIMIT ' . ($this->limit instanceof SqlSerializable ? $this->limit->serialize($formatter) : $this->limit);
        }

        return $result;
    }

}
