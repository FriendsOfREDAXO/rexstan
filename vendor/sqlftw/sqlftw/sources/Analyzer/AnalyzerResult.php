<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer;

use LogicException;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Sql\Statement;
use function spl_object_id;

class AnalyzerResult
{

    private int $id;

    private string $message;

    private ?AnalyzerRule $rule = null;

    private ?Statement $statement = null;

    private ?SqlMode $mode;

    private int $severity;

    private ?bool $autoRepair;

    /** @var non-empty-list<Statement>|null */
    private ?array $repairStatements;

    /**
     * @param non-empty-list<Statement>|null $repairStatements
     */
    public function __construct(
        string $message,
        ?int $severity = null,
        ?bool $autoRepair = AutoRepair::NOT_POSSIBLE,
        ?array $repairStatements = null
    )
    {
        if ($severity === null) {
            $severity = AnalyzerResultSeverity::ERROR;
        }

        $this->id = spl_object_id($this);
        $this->message = $message;
        $this->severity = $severity;
        $this->autoRepair = $autoRepair;
        $this->repairStatements = $repairStatements;
    }

    public function setContext(AnalyzerRule $rule, Statement $statement, SqlMode $mode): self
    {
        $this->rule = $rule;
        $this->statement = $statement;
        $this->mode = $mode;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRule(): AnalyzerRule
    {
        if ($this->rule === null) {
            throw new LogicException('Context has not been set.');
        }

        return $this->rule;
    }

    public function getStatement(): Statement
    {
        if ($this->statement === null) {
            throw new LogicException('Context has not been set.');
        }

        return $this->statement;
    }

    public function getMode(): SqlMode
    {
        if ($this->mode === null) {
            throw new LogicException('Context has not been set.');
        }

        return $this->mode;
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }

    public function canBeAutoRepaired(): bool
    {
        return $this->autoRepair === AutoRepair::POSSIBLE;
    }

    public function isAutoRepaired(): bool
    {
        return $this->autoRepair === AutoRepair::REPAIRED;
    }

    /**
     * @return list<Statement>
     */
    public function getRepairStatements(): array
    {
        return $this->repairStatements ?? [];
    }

}
