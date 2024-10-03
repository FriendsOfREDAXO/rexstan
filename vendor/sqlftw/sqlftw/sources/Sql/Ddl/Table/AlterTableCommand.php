<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AlterTableAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RenameToAction;
use SqlFtw\Sql\Ddl\Table\Alter\AlterActionsList;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableAlgorithm;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableLock;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableOption;
use SqlFtw\Sql\Ddl\Table\Option\TableOption;
use SqlFtw\Sql\Ddl\Table\Option\TableOptionsList;
use SqlFtw\Sql\Ddl\Table\Partition\PartitioningDefinition;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;
use function assert;
use function is_array;
use function is_bool;
use function rtrim;
use function trim;

/**
 * @phpstan-import-type TableOptionValue from TableOption
 */
class AlterTableCommand extends StatementImpl implements DdlTableCommand
{

    private ObjectIdentifier $name;

    private AlterActionsList $actions;

    /** @var array<AlterTableOption::*, bool|AlterTableLock|AlterTableAlgorithm> */
    private array $alterOptions;

    private ?TableOptionsList $tableOptions;

    private ?PartitioningDefinition $partitioning;

    /**
     * @param AlterActionsList|list<AlterTableAction> $actions
     * @param array<AlterTableOption::*, bool|AlterTableLock|AlterTableAlgorithm> $alterOptions
     * @param TableOptionsList|array<TableOption::*, TableOptionValue>|null $tableOptions
     */
    public function __construct(
        ObjectIdentifier $name,
        $actions = [],
        array $alterOptions = [],
        $tableOptions = null,
        ?PartitioningDefinition $partitioning = null
    ) {
        if ($alterOptions !== []) {
            foreach ($alterOptions as $option => $value) {
                AlterTableOption::checkValue($option);
            }
        }
        if ($tableOptions === []) {
            $tableOptions = null;
        }

        $this->name = $name;
        $this->actions = is_array($actions) ? new AlterActionsList($actions) : $actions;
        $this->alterOptions = $alterOptions;
        $this->tableOptions = is_array($tableOptions) ? new TableOptionsList($tableOptions) : $tableOptions;
        $this->partitioning = $partitioning;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->name;
    }

    public function getActionsList(): AlterActionsList
    {
        return $this->actions;
    }

    /**
     * @return list<AlterTableAction>
     */
    public function getActions(): array
    {
        return $this->actions->getActions();
    }

    /**
     * @return array<AlterTableOption::*, bool|AlterTableLock|AlterTableAlgorithm>
     */
    public function getAlterOptions(): array
    {
        return $this->alterOptions;
    }

    /**
     * @return array<TableOption::*, TableOptionValue|null>
     */
    public function getOptions(): array
    {
        return $this->tableOptions !== null ? $this->tableOptions->getOptions() : [];
    }

    public function getOptionsList(): TableOptionsList
    {
        return $this->tableOptions ?? new TableOptionsList([]);
    }

    public function getRenameAction(): ?RenameToAction
    {
        /** @var RenameToAction|null $rename */
        $rename = $this->actions->filter(RenameToAction::class)[0] ?? null;

        return $rename;
    }

    public function getPartitioning(): ?PartitioningDefinition
    {
        return $this->partitioning;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER ';
        if (isset($this->alterOptions[AlterTableOption::ONLINE])) {
            $result .= 'ONLINE ';
        }
        $result .= 'TABLE ' . $this->name->serialize($formatter);

        $result .= $this->actions->serialize($formatter);

        if ($this->tableOptions !== null) {
            if (!$this->actions->isEmpty()) {
                $result .= ',';
            }
            $result .= "\n" . $formatter->indent . $this->tableOptions->serialize($formatter, ",\n", ' ');
        }

        if (($this->tableOptions !== null || !$this->actions->isEmpty()) && $this->alterOptions !== []) {
            $result .= ',';
        }

        foreach ($this->alterOptions as $option => $value) {
            if ($option === AlterTableOption::ONLINE) {
                continue;
            } elseif ($option === AlterTableOption::FORCE) {
                $result .= "\n" . $formatter->indent . 'FORCE, ';
            } elseif ($option === AlterTableOption::VALIDATION) {
                assert(is_bool($value));
                $result .= "\n" . $formatter->indent . ($value ? 'WITH' : 'WITHOUT') . ' VALIDATION, ';
            } else {
                $result .= "\n" . $formatter->indent . $option . ' ' . $formatter->formatValue($value) . ',';
            }
        }

        $result = rtrim($result, ',');

        if ($this->partitioning !== null) {
            $result .= "\n" . $this->partitioning->serialize($formatter);
        }

        return trim(rtrim($result, ' '), ',');
    }

}
