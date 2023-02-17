<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\OptimizerHint;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\InvalidDefinitionException;

/**
 * @phpstan-import-type IndexLevelHintType from OptimizerHintType
 */
class IndexLevelHint implements OptimizerHint
{

    /** @var IndexLevelHintType&string */
    private string $type;

    private ?string $queryBlock;

    private ?HintTableIdentifier $table;

    /** @var non-empty-list<string>|null */
    private ?array $indexes;

    /**
     * @param IndexLevelHintType&string $type
     * @param non-empty-list<string> $indexes
     */
    public function __construct(string $type, ?string $queryBlock, ?HintTableIdentifier $table, ?array $indexes = null)
    {
        if ($queryBlock !== null) {
            if ($table instanceof NameWithQueryBlock) {
                throw new InvalidDefinitionException('Cannot use names with query block, when query block is defined for all names.');
            }
        }

        $this->type = $type;
        $this->queryBlock = $queryBlock;
        $this->table = $table;
        $this->indexes = $indexes;
    }

    /**
     * @return IndexLevelHintType&string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getQueryBlock(): ?string
    {
        return $this->queryBlock;
    }

    public function getTable(): ?HintTableIdentifier
    {
        return $this->table;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getIndexes(): ?array
    {
        return $this->indexes;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->type . '('
            . ($this->queryBlock !== null ? '@' . $formatter->formatName($this->queryBlock) . ' ' : '')
            . ($this->table !== null ? $this->table->serialize($formatter) : '')
            . ($this->indexes !== null ? ' ' . $formatter->formatNamesList($this->indexes) : '') . ')';
    }

}
