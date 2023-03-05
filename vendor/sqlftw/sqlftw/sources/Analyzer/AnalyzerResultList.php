<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer;

use SqlFtw\Sql\Statement;
use function count;
use function get_class;
use function ksort;

class AnalyzerResultList
{

    /** @var AnalyzerResult[][] */
    private array $results = [];

    private int $parserErrorsCount = 0;

    private int $parserWarningsCount = 0;

    private int $errorsCount = 0;

    private int $warningsCount = 0;

    private int $noticesCount = 0;

    private int $repairedCount = 0;

    private int $repairableCount = 0;

    /** @var AnalyzerResult[][][] */
    private array $index = [];

    /** @var Statement[] */
    private array $repairStatements = [];

    /**
     * @param AnalyzerResult[] $results
     */
    public function addResults(array $results): void
    {
        if ($results === []) {
            return;
        }
        $this->results[] = $results;
        foreach ($results as $result) {
            $severity = $result->getSeverity();
            $ruleClass = get_class($result->getRule());
            $this->index[$ruleClass][$severity][$result->getId()] = $result;

            if ($result->isAutoRepaired()) {
                $this->repairedCount++;
            } elseif ($severity === AnalyzerResultSeverity::PARSER_ERROR) {
                $this->parserErrorsCount++;
            } elseif ($severity === AnalyzerResultSeverity::ERROR) {
                $this->errorsCount++;
            } elseif ($severity === AnalyzerResultSeverity::WARNING) {
                $this->warningsCount++;
            } elseif ($severity === AnalyzerResultSeverity::PARSER_WARNING) {
                $this->parserWarningsCount++;
            } elseif ($severity === AnalyzerResultSeverity::NOTICE) {
                $this->noticesCount++;
            } else {
                $this->errorsCount++;
            }
            if ($result->canBeAutoRepaired()) {
                $this->repairableCount++;
            }
            foreach ($result->getRepairStatements() as $command) {
                $this->repairStatements[] = $command;
            }
        }
        ksort($this->index);
    }

    /**
     * @return AnalyzerResult[][]
     */
    public function getResults(?callable $filter = null): array
    {
        if ($filter === null) {
            return $this->results;
        }
        $results = [];
        foreach ($this->results as $commandKey => $commandResults) {
            foreach ($commandResults as $key => $result) {
                if ($filter($result)) {
                    $results[$commandKey][$key] = $result;
                }
            }
        }
        return $results;
    }

    /**
     * @return AnalyzerResult[][][]
     */
    public function getIndex(): array
    {
        return $this->index;
    }

    /**
     * @return AnalyzerResult[][]
     */
    public function getParserErrors(): array
    {
        return $this->getResults(static function (AnalyzerResult $result) {
            return $result->getSeverity() === AnalyzerResultSeverity::PARSER_ERROR;
        });
    }

    public function hasParserErrors(): bool
    {
        return $this->parserErrorsCount > 0;
    }

    public function countParserErrors(): int
    {
        return $this->parserErrorsCount;
    }

    /**
     * @return AnalyzerResult[][]
     */
    public function getErrors(): array
    {
        return $this->getResults(static function (AnalyzerResult $result) {
            return $result->getSeverity() === AnalyzerResultSeverity::ERROR && !$result->isAutoRepaired();
        });
    }

    public function hasErrors(): bool
    {
        return $this->errorsCount > 0;
    }

    public function countErrors(): int
    {
        return $this->errorsCount;
    }

    /**
     * @return AnalyzerResult[][]
     */
    public function getWarnings(): array
    {
        return $this->getResults(static function (AnalyzerResult $result) {
            return $result->getSeverity() === AnalyzerResultSeverity::WARNING;
        });
    }

    public function hasWarnings(): bool
    {
        return $this->warningsCount > 0;
    }

    public function countWarnings(): int
    {
        return $this->warningsCount;
    }

    /**
     * @return AnalyzerResult[][]
     */
    public function getParserWarnings(): array
    {
        return $this->getResults(static function (AnalyzerResult $result) {
            return $result->getSeverity() === AnalyzerResultSeverity::PARSER_WARNING;
        });
    }

    public function hasParserWarnings(): bool
    {
        return $this->parserWarningsCount > 0;
    }

    public function countParserWarnings(): int
    {
        return $this->parserWarningsCount;
    }

    public function hasNotices(): bool
    {
        return $this->noticesCount > 0;
    }

    public function countNotices(): int
    {
        return $this->noticesCount;
    }

    public function hasRepaired(): bool
    {
        return $this->repairedCount > 0;
    }

    public function countRepaired(): int
    {
        return $this->repairedCount;
    }

    public function hasRepairable(): bool
    {
        return $this->repairableCount > 0;
    }

    public function countRepairable(): int
    {
        return $this->repairableCount;
    }

    public function hasRepairCommands(): bool
    {
        return count($this->repairStatements) > 0;
    }

    /**
     * @return Statement[]
     */
    public function getRepairStatements(): array
    {
        return $this->repairStatements;
    }

}
