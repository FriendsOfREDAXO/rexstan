<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Analyzer\Rules\Charset;

use SqlFtw\Analyzer\AnalyzerResult;
use SqlFtw\Analyzer\SimpleContext;
use SqlFtw\Analyzer\SimpleRule;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Ddl\Schema\AlterSchemaCommand;
use SqlFtw\Sql\Ddl\Schema\CreateSchemaCommand;
use SqlFtw\Sql\Ddl\Schema\SchemaCommand;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ConvertToCharsetAction;
use SqlFtw\Sql\Ddl\Table\AlterTableCommand;
use SqlFtw\Sql\Ddl\Table\CreateTableCommand;
use SqlFtw\Sql\Ddl\Table\Option\TableOption;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\TableCommand;
use function count;

class CharsetAndCollationCompatibilityRule implements SimpleRule
{

    /**
     * @return list<AnalyzerResult>
     */
    public function process(Statement $statement, SimpleContext $context, int $flags): array
    {
        if ($statement instanceof CreateSchemaCommand || $statement instanceof AlterSchemaCommand) {
            return $this->processSchema($statement, $context);
        } elseif ($statement instanceof CreateTableCommand || $statement instanceof AlterTableCommand) {
            return $this->processTable($statement, $context);
        }

        return [];
    }

    /**
     * @param CreateSchemaCommand|AlterSchemaCommand $command
     * @return list<AnalyzerResult>
     */
    private function processSchema(SchemaCommand $command, SimpleContext $context): array
    {
        $results = [];
        $options = $command->getOptions();
        $charset = $options->getCharset();
        $collation = $options->getCollation();
        if ($charset !== null && $collation !== null && !$charset->supportsCollation($collation)) {
            $results[] = new AnalyzerResult("Mismatch between database charset ({$charset->getValue()}) and collation ({$collation->getValue()}).");
        }

        return $results;
    }

    /**
     * @param CreateTableCommand|AlterTableCommand $command
     * @return list<AnalyzerResult>
     */
    private function processTable(TableCommand $command, SimpleContext $context): array
    {
        $results = [];
        $options = $command->getOptionsList();
        /** @var Charset|DefaultLiteral $charset */
        $charset = $options->get(TableOption::CHARACTER_SET);
        if ($charset instanceof DefaultLiteral) {
            // todo: should take from schema
            $charset = null;
        }
        /** @var Collation|DefaultLiteral $collation */
        $collation = $options->get(TableOption::COLLATE);
        if ($collation instanceof DefaultLiteral) {
            // todo: should take from schema
            $collation = null;
        }
        if ($charset !== null && $collation !== null && !$charset->supportsCollation($collation)) {
            $results[] = new AnalyzerResult("Mismatch between table charset ({$charset->getValue()}) and collation ({$collation->getValue()}).");
        }

        if ($command instanceof AlterTableCommand) {
            /** @var list<ConvertToCharsetAction> $convertActions */
            $convertActions = $command->getActionsList()->filter(ConvertToCharsetAction::class);
            $convertAction = $convertActions[0] ?? null;
            if ($convertAction !== null) {
                $convertCharset = $convertAction->getCharset();
                if ($convertCharset instanceof DefaultLiteral) {
                    // todo: should take from schema
                    $convertCharset = null;
                }
                if ($convertCharset !== null) {
                    if ($charset !== null && !$convertCharset->equals($charset)) {
                        $results[] = new AnalyzerResult("Conflict between conversion charset ({$convertCharset->getValue()}) and table charset ({$charset->getValue()}).");
                    }
                    if ($collation !== null && !$convertCharset->supportsCollation($collation)) {
                        $results[] = new AnalyzerResult("Mismatch between conversion charset ({$convertCharset->getValue()}) and table collation ({$collation->getValue()}).");
                    }
                    $convertCollation = $convertAction->getCollation();
                    //!$convertAction->getCharset() instanceof DefaultLiteral
                    if ($convertCollation !== null && !$convertCharset->supportsCollation($convertCollation)) {
                        $results[] = new AnalyzerResult("Mismatch between conversion charset ({$convertCharset->getValue()}) and conversion collation ({$convertCollation->getValue()}).");
                    }
                    if (count($convertActions) > 1) {
                        foreach ($convertActions as $otherAction) {
                            $otherConvertCharset = $otherAction->getCharset();
                            if ($otherConvertCharset instanceof DefaultLiteral) {
                                // todo: should take from schema
                                $otherConvertCharset = null;
                            }
                            if ($otherConvertCharset !== null && !$convertCharset->equals($otherConvertCharset)) {
                                $results[] = new AnalyzerResult("Conflict between conversion charset ({$convertCharset->getValue()}) and other conversion charset ({$otherConvertCharset->getValue()}).");
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($command->getColumns() as $column) {
                $type = $column->getType();
                $columnCharset = $type->getCharset();
                $columnCollation = $type->getCollation();
                if ($columnCharset !== null && $columnCollation !== null && !$columnCharset->supportsCollation($columnCollation)) {
                    $results[] = new AnalyzerResult("Mismatch between charset ({$columnCharset->getValue()}) and collation ({$columnCollation->getValue()}) on column {$column->getName()}.");
                }
            }
        }

        return $results;
    }

}
