<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Insert;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\OptimizerHint\OptimizerHint;
use SqlFtw\Sql\Expression\ColumnIdentifier;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use function array_map;
use function implode;

class ReplaceValuesCommand extends InsertOrReplaceCommand implements ReplaceCommand
{

    /** @var non-empty-list<list<ExpressionNode>> */
    private array $rows;

    /**
     * @param non-empty-list<list<ExpressionNode>> $rows
     * @param list<ColumnIdentifier>|null $columns
     * @param non-empty-list<string>|null $partitions
     * @param non-empty-list<OptimizerHint>|null $optimizerHints
     */
    public function __construct(
        ObjectIdentifier $table,
        array $rows,
        ?array $columns = null,
        ?array $partitions = null,
        ?InsertPriority $priority = null,
        bool $ignore = false,
        ?array $optimizerHints = null
    ) {
        parent::__construct($table, $columns, $partitions, $priority, $ignore, $optimizerHints);

        $this->rows = $rows;
    }

    /**
     * @return non-empty-list<list<ExpressionNode>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'REPLACE' . $this->serializeBody($formatter);

        $result .= ' VALUES ' . implode(', ', array_map(static function (array $values) use ($formatter): string {
            return '(' . implode(', ', array_map(static function (ExpressionNode $value) use ($formatter): string {
                return $value->serialize($formatter);
            }, $values)) . ')';
        }, $this->rows));

        return $result;
    }

}
