<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Ddl;

use Dogma\Math\PowersOfTwo;
use SqlFtw\Parser\Dml\QueryParser;
use SqlFtw\Parser\ExpressionParser;
use SqlFtw\Parser\InvalidValueException;
use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Parser\TokenType;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Ddl\StorageType;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddColumnAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddColumnsAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddConstraintAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddForeignKeyAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddIndexAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddPartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AddPartitionNumberAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AlterCheckAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AlterColumnAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AlterConstraintAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AlterIndexAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\AnalyzePartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ChangeColumnAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\CheckPartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\CoalescePartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ConvertToCharsetAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DisableKeysAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DiscardPartitionTablespaceAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DiscardTablespaceAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropCheckAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropColumnAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropConstraintAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropForeignKeyAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropIndexAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropPartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\DropPrimaryKeyAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\EnableKeysAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ExchangePartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ImportPartitionTablespaceAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ImportTablespaceAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ModifyColumnAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\OptimizePartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\OrderByAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RebuildPartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RemovePartitioningAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RenameColumnAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RenameIndexAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RenameToAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\ReorganizePartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\RepairPartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\TruncatePartitionAction;
use SqlFtw\Sql\Ddl\Table\Alter\Action\UpgradePartitioningAction;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableActionType;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableAlgorithm;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableLock;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableOption;
use SqlFtw\Sql\Ddl\Table\AlterTableCommand;
use SqlFtw\Sql\Ddl\Table\AnyCreateTableCommand;
use SqlFtw\Sql\Ddl\Table\Column\ColumnDefinition;
use SqlFtw\Sql\Ddl\Table\Column\ColumnFormat;
use SqlFtw\Sql\Ddl\Table\Column\GeneratedColumnType;
use SqlFtw\Sql\Ddl\Table\Constraint\CheckDefinition;
use SqlFtw\Sql\Ddl\Table\Constraint\ConstraintDefinition;
use SqlFtw\Sql\Ddl\Table\Constraint\ConstraintType;
use SqlFtw\Sql\Ddl\Table\Constraint\ForeignKeyAction;
use SqlFtw\Sql\Ddl\Table\Constraint\ForeignKeyDefinition;
use SqlFtw\Sql\Ddl\Table\Constraint\ForeignKeyMatchType;
use SqlFtw\Sql\Ddl\Table\Constraint\ReferenceDefinition;
use SqlFtw\Sql\Ddl\Table\CreateTableCommand;
use SqlFtw\Sql\Ddl\Table\CreateTableLikeCommand;
use SqlFtw\Sql\Ddl\Table\DropTableCommand;
use SqlFtw\Sql\Ddl\Table\Index\IndexDefinition;
use SqlFtw\Sql\Ddl\Table\Index\IndexType;
use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Ddl\Table\Option\TableCompression;
use SqlFtw\Sql\Ddl\Table\Option\TableInsertMethod;
use SqlFtw\Sql\Ddl\Table\Option\TableOption;
use SqlFtw\Sql\Ddl\Table\Option\TableRowFormat;
use SqlFtw\Sql\Ddl\Table\Option\ThreeStateValue;
use SqlFtw\Sql\Ddl\Table\Partition\PartitionDefinition;
use SqlFtw\Sql\Ddl\Table\Partition\PartitioningCondition;
use SqlFtw\Sql\Ddl\Table\Partition\PartitioningConditionType;
use SqlFtw\Sql\Ddl\Table\Partition\PartitioningDefinition;
use SqlFtw\Sql\Ddl\Table\Partition\PartitionOption;
use SqlFtw\Sql\Ddl\Table\RenameTableCommand;
use SqlFtw\Sql\Ddl\Table\TableItem;
use SqlFtw\Sql\Ddl\Table\TruncateTableCommand;
use SqlFtw\Sql\Dml\DuplicateOption;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\BuiltInFunction;
use SqlFtw\Sql\Expression\ColumnType;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\Expression\ListExpression;
use SqlFtw\Sql\Expression\MaxValueLiteral;
use SqlFtw\Sql\Expression\NullLiteral;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\Parentheses;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\StringLiteral;
use SqlFtw\Sql\Expression\UintLiteral;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\InvalidEnumValueException;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlMode;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\SubqueryType;
use function array_values;
use function count;
use function strlen;
use function strtoupper;

/**
 * @phpstan-import-type TableOptionValue from TableOption
 */
class TableCommandsParser
{

    private ExpressionParser $expressionParser;

    private IndexCommandsParser $indexCommandsParser;

    private QueryParser $queryParser;

    public function __construct(
        ExpressionParser $expressionParser,
        IndexCommandsParser $indexCommandsParser,
        QueryParser $queryParser
    ) {
        $this->expressionParser = $expressionParser;
        $this->indexCommandsParser = $indexCommandsParser;
        $this->queryParser = $queryParser;
    }

    /**
     * ALTER TABLE tbl_name
     *     [alter_specification [, alter_specification] ...]
     *     [partition_options]
     *
     * alter_specification:
     *     table_options
     *   | ADD [COLUMN] col_name column_definition
     *         [FIRST | AFTER col_name ]
     *   | ADD [COLUMN] (col_name column_definition, ...)
     *   | ADD {INDEX|KEY} [index_name]
     *         [index_type] (index_col_name, ...) [index_option] ...
     *   | ADD [CONSTRAINT [symbol]] PRIMARY KEY
     *         [index_type] (index_col_name, ...) [index_option] ...
     *   | ADD [CONSTRAINT [symbol]]
     *         UNIQUE [INDEX|KEY] [index_name]
     *         [index_type] (index_col_name, ...) [index_option] ...
     *   | ADD FULLTEXT [INDEX|KEY] [index_name]
     *         (index_col_name, ...) [index_option] ...
     *   | ADD SPATIAL [INDEX|KEY] [index_name]
     *         (index_col_name, ...) [index_option] ...
     *   | ADD [CONSTRAINT [symbol]]
     *         FOREIGN KEY [index_name] (index_col_name, ...)
     *         reference_definition
     *   | ALGORITHM [=] {DEFAULT|INPLACE|COPY}
     *   | ALTER [COLUMN] col_name {SET DEFAULT literal | DROP DEFAULT | SET INVISIBLE | SET VISIBLE}
     *   | CHANGE [COLUMN] old_col_name new_col_name column_definition
     *     [FIRST|AFTER col_name]
     *   | LOCK [=] {DEFAULT|NONE|SHARED|EXCLUSIVE}
     *   | MODIFY [COLUMN] col_name column_definition
     *         [FIRST | AFTER col_name]
     *   | DROP [COLUMN] col_name
     *   | DROP PRIMARY KEY
     *   | DROP {INDEX|KEY} index_name
     *   | DROP FOREIGN KEY fk_symbol
     *   | ALTER INDEX index_name {VISIBLE | INVISIBLE}
     *   | DISABLE KEYS
     *   | ENABLE KEYS
     *   | RENAME COLUMN old_col_name TO new_col_name
     *   | RENAME [TO|AS] new_tbl_name
     *   | RENAME {INDEX|KEY} old_index_name TO new_index_name
     *   | ORDER BY col_name [ASC | DESC] [, col_name [ASC | DESC]] ...
     *   | CONVERT TO CHARACTER SET charset_name [COLLATE collation_name]
     *   | [DEFAULT] CHARACTER SET [=] charset_name [COLLATE [=] collation_name]
     *   | DISCARD TABLESPACE
     *   | IMPORT TABLESPACE
     *   | FORCE
     *   | {WITHOUT|WITH} VALIDATION
     *   | ADD PARTITION (partition_definition[, ...])
     *   | ADD PARTITION PARTITIONS n // todo: undocumented. adding partitions when on PARTITION BY HASH
     *   | DROP PARTITION partition_names
     *   | DISCARD PARTITION {partition_names | ALL} TABLESPACE
     *   | IMPORT PARTITION {partition_names | ALL} TABLESPACE
     *   | TRUNCATE PARTITION {partition_names | ALL}
     *   | COALESCE PARTITION number
     *   | REORGANIZE PARTITION [partition_names INTO (partition_definitions)]
     *   | EXCHANGE PARTITION partition_name WITH TABLE tbl_name [{WITH|WITHOUT} VALIDATION]
     *   | ANALYZE PARTITION {partition_names | ALL}
     *   | CHECK PARTITION {partition_names | ALL}
     *   | OPTIMIZE PARTITION {partition_names | ALL}
     *   | REBUILD PARTITION {partition_names | ALL}
     *   | REPAIR PARTITION {partition_names | ALL}
     *   | REMOVE PARTITIONING
     *   | UPGRADE PARTITIONING
     *
     * table_options:
     *     table_option [[,] table_option] ...  (see CREATE TABLE options)
     */
    public function parseAlterTable(TokenList $tokenList): AlterTableCommand
    {
        $actions = [];
        $alterOptions = [];
        $tableOptions = [];

        $tokenList->expectKeyword(Keyword::ALTER);
        if ($tokenList->hasKeyword(Keyword::ONLINE)) {
            $alterOptions[AlterTableOption::ONLINE] = true;
        }
        $tokenList->expectKeyword(Keyword::TABLE);
        $name = $tokenList->expectObjectIdentifier();

        do {
            $position = $tokenList->getPosition();
            $keyword = $tokenList->getKeyword();
            switch ($keyword) {
                case Keyword::ADD:
                    $second = $tokenList->get(TokenType::KEYWORD);
                    $secondValue = $second !== null ? strtoupper($second->value) : null;
                    switch ($secondValue) {
                        case null:
                        case Keyword::COLUMN:
                            if ($tokenList->hasSymbol('(')) {
                                // ADD [COLUMN] (col_name column_definition, ...)
                                // note: apparently index can be added in ADD COLUMN. not documented.
                                // from tests: ALTER TABLE st1 ADD COLUMN (c2 INT GENERATED ALWAYS AS (c1+1) STORED, INDEX(c2));
                                // from tests: ALTER TABLE mysqltest.my_socket_summary ADD COLUMN (n INT AUTO_INCREMENT, PRIMARY KEY(n));
                                $addColumns = [];
                                do {
                                    if ($tokenList->hasKeyword(Keyword::INDEX)) {
                                        $addColumns[] = $this->parseIndex($tokenList);
                                    } elseif ($tokenList->hasKeyword(Keyword::PRIMARY)) {
                                        $addColumns[] = $this->parseIndex($tokenList, true);
                                    } else {
                                        $addColumns[] = $this->parseColumn($tokenList);
                                    }
                                } while ($tokenList->hasSymbol(','));
                                $actions[] = new AddColumnsAction($addColumns);
                                $tokenList->expectSymbol(')');
                            } else {
                                // ADD [COLUMN] col_name column_definition [FIRST | AFTER col_name ]
                                $column = $this->parseColumn($tokenList);
                                $after = null;
                                if ($tokenList->hasKeyword(Keyword::FIRST)) {
                                    $after = ModifyColumnAction::FIRST;
                                } elseif ($tokenList->hasKeyword(Keyword::AFTER)) {
                                    $after = $tokenList->expectName(EntityType::COLUMN);
                                }
                                $actions[] = new AddColumnAction($column, $after);
                            }
                            break;
                        case Keyword::CONSTRAINT:
                            // ADD [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name, ...) reference_definition
                            // ADD [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] (index_col_name, ...) [index_option] ...
                            // ADD [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name, ...) [index_option] ...
                            $actions[] = new AddConstraintAction($this->parseConstraint($tokenList->rewind(-1)));
                            break;
                        case Keyword::FOREIGN:
                            // ADD [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name, ...) reference_definition
                            $actions[] = new AddForeignKeyAction($this->parseForeignKey($tokenList->rewind(-1)));
                            break;
                        case Keyword::PRIMARY:
                            // ADD [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name, ...) [index_option] ...
                            $index = $this->parseIndex($tokenList, true);
                            $actions[] = new AddIndexAction($index);
                            break;
                        case Keyword::FULLTEXT:
                        case Keyword::INDEX:
                        case Keyword::KEY:
                        case Keyword::SPATIAL:
                        case Keyword::UNIQUE:
                            // ADD FULLTEXT [INDEX|KEY] [index_name] (index_col_name, ...) [index_option] ...
                            // ADD {INDEX|KEY} [index_name] [index_type] (index_col_name, ...) [index_option] ...
                            // ADD SPATIAL [INDEX|KEY] [index_name] (index_col_name, ...) [index_option] ...
                            // ADD [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] (index_col_name, ...) [index_option] ...
                            $index = $this->parseIndex($tokenList->rewind(-1));
                            $actions[] = new AddIndexAction($index);
                            break;
                        case Keyword::PARTITION:
                            if ($tokenList->hasKeyword(Keyword::PARTITIONS)) {
                                // ADD PARTITION PARTITIONS number
                                $actions[] = new AddPartitionNumberAction((int) $tokenList->expectUnsignedInt());
                            } else {
                                // ADD PARTITION (partition_definition)
                                $tokenList->expectSymbol('(');
                                $partitions = [];
                                do {
                                    $partitions[] = $this->parsePartitionDefinition($tokenList);
                                } while ($tokenList->hasSymbol(','));
                                $tokenList->expectSymbol(')');
                                $actions[] = new AddPartitionAction($partitions);
                            }
                            break;
                        default:
                            // keyword used as a column name
                            if ($second !== null && ($second->type & TokenType::RESERVED) === 0) {
                                // ADD [COLUMN] col_name column_definition [FIRST | AFTER col_name ]
                                $column = $this->parseColumn($tokenList->rewind(-1));
                                $after = null;
                                if ($tokenList->hasKeyword(Keyword::FIRST)) {
                                    $after = ModifyColumnAction::FIRST;
                                } elseif ($tokenList->hasKeyword(Keyword::AFTER)) {
                                    $after = $tokenList->expectName(EntityType::COLUMN);
                                }
                                $actions[] = new AddColumnAction($column, $after);
                            } else {
                                // phpcs:disable PSR2.Methods.FunctionCallSignature.MultipleArguments
                                $tokenList->missingAnyKeyword(
                                    Keyword::COLUMN, Keyword::CONSTRAINT, Keyword::FOREIGN, Keyword::FULLTEXT, Keyword::INDEX,
                                    Keyword::KEY, Keyword::PARTITION, Keyword::PRIMARY, Keyword::SPATIAL, Keyword::UNIQUE
                                );
                            }
                    }
                    break;
                case Keyword::ALGORITHM:
                    // ALGORITHM [=] {DEFAULT|INPLACE|COPY}
                    $tokenList->passSymbol('=');
                    if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                        $alterOptions[Keyword::ALGORITHM] = new AlterTableAlgorithm(AlterTableAlgorithm::DEFAULT);
                    } else {
                        $alterOptions[Keyword::ALGORITHM] = $tokenList->expectNameOrStringEnum(AlterTableAlgorithm::class);
                    }
                    break;
                case Keyword::ALTER:
                    if ($tokenList->hasKeyword(Keyword::INDEX)) {
                        // ALTER INDEX index_name {VISIBLE | INVISIBLE}
                        $index = $tokenList->expectName(EntityType::INDEX);
                        $visible = $tokenList->expectAnyKeyword(Keyword::VISIBLE, Keyword::INVISIBLE);
                        $actions[] = new AlterIndexAction($index, $visible === Keyword::VISIBLE);
                    } elseif ($tokenList->hasKeyword(Keyword::CONSTRAINT)) {
                        // ALTER CONSTRAINT symbol [NOT] ENFORCED
                        $constraint = $tokenList->expectName(EntityType::CONSTRAINT);
                        $enforced = true;
                        if ($tokenList->hasKeyword(Keyword::NOT)) {
                            $enforced = false;
                        }
                        $tokenList->expectKeyword(Keyword::ENFORCED);
                        $actions[] = new AlterConstraintAction($constraint, $enforced);
                    } elseif ($tokenList->hasKeyword(Keyword::CHECK)) {
                        // ALTER CHECK symbol [NOT] ENFORCED
                        $check = $tokenList->expectName(EntityType::CONSTRAINT);
                        $enforced = true;
                        if ($tokenList->hasKeyword(Keyword::NOT)) {
                            $enforced = false;
                        }
                        $tokenList->expectKeyword(Keyword::ENFORCED);
                        $actions[] = new AlterCheckAction($check, $enforced);
                    } else {
                        // ALTER [COLUMN] col_name {SET DEFAULT literal | DROP DEFAULT | SET INVISIBLE | SET VISIBLE}
                        $tokenList->passKeyword(Keyword::COLUMN);
                        $column = $tokenList->expectName(EntityType::COLUMN);
                        $default = $visible = null;
                        if ($tokenList->hasKeywords(Keyword::SET, Keyword::DEFAULT)) {
                            if ($tokenList->hasSymbol('(')) {
                                $default = $this->expressionParser->parseExpression($tokenList);
                                $tokenList->expectSymbol(')');
                            } else {
                                $default = $this->expressionParser->parseLiteral($tokenList);
                            }
                        } elseif ($tokenList->hasKeywords(Keyword::DROP, Keyword::DEFAULT)) {
                            $default = false;
                        } elseif ($tokenList->hasKeywords(Keyword::SET, Keyword::INVISIBLE)) {
                            $visible = false;
                        } elseif ($tokenList->hasKeywords(Keyword::SET, Keyword::VISIBLE)) {
                            $visible = true;
                        } else {
                            throw new ParserException('Expected SET DEFAULT, DROP DEFAULT, SET INVISIBLE or SET VISIBLE', $tokenList);
                        }
                        $actions[] = new AlterColumnAction($column, $default, $visible);
                    }
                    break;
                case Keyword::ANALYZE:
                    // ANALYZE PARTITION {partition_names | ALL}
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partitions = $this->parsePartitionNames($tokenList);
                    $actions[] = new AnalyzePartitionAction($partitions);
                    break;
                case Keyword::CHANGE:
                    // CHANGE [COLUMN] old_col_name new_col_name column_definition [FIRST|AFTER col_name]
                    $tokenList->passKeyword(Keyword::COLUMN);
                    $oldName = $tokenList->expectName(EntityType::COLUMN);
                    $column = $this->parseColumn($tokenList);
                    $after = null;
                    if ($tokenList->hasKeyword(Keyword::FIRST)) {
                        $after = ModifyColumnAction::FIRST;
                    } elseif ($tokenList->hasKeyword(Keyword::AFTER)) {
                        $after = $tokenList->expectName(EntityType::COLUMN);
                    }
                    $actions[] = new ChangeColumnAction($oldName, $column, $after);
                    break;
                case Keyword::CHECK:
                    // CHECK PARTITION {partition_names | ALL}
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partitions = $this->parsePartitionNames($tokenList);
                    $actions[] = new CheckPartitionAction($partitions);
                    break;
                case Keyword::COALESCE:
                    // COALESCE PARTITION number
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $actions[] = new CoalescePartitionAction((int) $tokenList->expectUnsignedInt());
                    break;
                case Keyword::CONVERT:
                    // CONVERT TO CHARACTER SET charset_name [COLLATE collation_name]
                    $tokenList->expectKeyword(Keyword::TO);
                    if (!$tokenList->hasKeyword(Keyword::CHARSET) && !$tokenList->hasKeywords(Keyword::CHAR, Keyword::SET)) {
                        $tokenList->expectKeywords(Keyword::CHARACTER, Keyword::SET);
                    }
                    if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                        $charset = new DefaultLiteral();
                    } else {
                        $charset = $tokenList->expectCharsetName();
                    }
                    $collation = null;
                    if ($tokenList->hasKeyword(Keyword::COLLATE)) {
                        $collation = $tokenList->expectCollationName();
                    }
                    $actions[] = new ConvertToCharsetAction($charset, $collation);
                    break;
                case Keyword::DISCARD:
                    $second = $tokenList->expectAnyKeyword(Keyword::TABLESPACE, Keyword::PARTITION);
                    if ($second === Keyword::TABLESPACE) {
                        // DISCARD TABLESPACE
                        $actions[] = new DiscardTablespaceAction();
                    } else {
                        // DISCARD PARTITION {partition_names | ALL} TABLESPACE
                        $partitions = $this->parsePartitionNames($tokenList);
                        $tokenList->expectKeyword(Keyword::TABLESPACE);
                        $actions[] = new DiscardPartitionTablespaceAction($partitions);
                    }
                    break;
                case Keyword::DISABLE:
                    // DISABLE KEYS
                    $tokenList->expectKeyword(Keyword::KEYS);
                    $actions[] = new DisableKeysAction();
                    break;
                case Keyword::DROP:
                    $second = $tokenList->get(TokenType::KEYWORD);
                    $keyword = $second !== null ? strtoupper($second->value) : null;
                    switch ($keyword) {
                        case Keyword::INDEX:
                        case Keyword::KEY:
                            // DROP {INDEX|KEY} index_name
                            $actions[] = new DropIndexAction($tokenList->expectName(EntityType::INDEX));
                            break;
                        case Keyword::FOREIGN:
                            // DROP FOREIGN KEY fk_symbol
                            $tokenList->expectKeyword(Keyword::KEY);
                            $actions[] = new DropForeignKeyAction($tokenList->expectName(EntityType::CONSTRAINT));
                            break;
                        case Keyword::CONSTRAINT:
                            // DROP CONSTRAINT symbol
                            $actions[] = new DropConstraintAction($tokenList->expectName(EntityType::CONSTRAINT));
                            break;
                        case Keyword::CHECK:
                            // DROP CHECK symbol
                            $actions[] = new DropCheckAction($tokenList->expectName(EntityType::CONSTRAINT));
                            break;
                        case Keyword::PARTITION:
                            // DROP PARTITION partition_names
                            $partitions = $this->parsePartitionNames($tokenList);
                            if ($partitions === null) {
                                $tokenList->missing('Expected specific partition names, found "ALL".');
                            }
                            $actions[] = new DropPartitionAction($partitions);
                            break;
                        case Keyword::PRIMARY:
                            // DROP PRIMARY KEY
                            $tokenList->expectKeyword(Keyword::KEY);
                            $actions[] = new DropPrimaryKeyAction();
                            break;
                        case null:
                        case Keyword::COLUMN:
                            // DROP [COLUMN] col_name
                            $tokenList->passKeyword(Keyword::COLUMN);
                            $actions[] = new DropColumnAction($tokenList->expectName(EntityType::COLUMN));
                            break;
                        default:
                            if ($second !== null && ($second->type & TokenType::UNQUOTED_NAME) !== 0) {
                                // DROP [COLUMN] col_name
                                $columnName = $second->value;
                                $actions[] = new DropColumnAction($columnName);
                            } else {
                                $tokenList->missingAnyKeyword(Keyword::COLUMN, Keyword::INDEX, Keyword::KEY, Keyword::FOREIGN, Keyword::PARTITION, Keyword::PRIMARY);
                            }
                    }
                    break;
                case Keyword::ENABLE:
                    // ENABLE KEYS
                    $tokenList->expectKeyword(Keyword::KEYS);
                    $actions[] = new EnableKeysAction();
                    break;
                case Keyword::EXCHANGE:
                    // EXCHANGE PARTITION partition_name WITH TABLE tbl_name [{WITH|WITHOUT} VALIDATION]
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partition = $tokenList->expectName(EntityType::PARTITION);
                    $tokenList->expectKeywords(Keyword::WITH, Keyword::TABLE);
                    $table = $tokenList->expectObjectIdentifier();
                    $validation = $tokenList->getAnyKeyword(Keyword::WITH, Keyword::WITHOUT);
                    if ($validation === Keyword::WITH) {
                        $tokenList->expectKeyword(Keyword::VALIDATION);
                        $validation = true;
                    } elseif ($validation === Keyword::WITHOUT) {
                        $tokenList->expectKeyword(Keyword::VALIDATION);
                        $validation = false;
                    } else {
                        $validation = null;
                    }
                    $actions[] = new ExchangePartitionAction($partition, $table, $validation);
                    break;
                case Keyword::FORCE:
                    // FORCE
                    $alterOptions[AlterTableOption::FORCE] = true;
                    break;
                case Keyword::IMPORT:
                    $second = $tokenList->expectAnyKeyword(Keyword::TABLESPACE, Keyword::PARTITION);
                    if ($second === Keyword::TABLESPACE) {
                        // IMPORT TABLESPACE
                        $actions[] = new ImportTablespaceAction();
                    } else {
                        // IMPORT PARTITION {partition_names | ALL} TABLESPACE
                        $partitions = $this->parsePartitionNames($tokenList);
                        $tokenList->expectKeyword(Keyword::TABLESPACE);
                        $actions[] = new ImportPartitionTablespaceAction($partitions);
                    }
                    break;
                case Keyword::LOCK:
                    // LOCK [=] {DEFAULT|NONE|SHARED|EXCLUSIVE}
                    $tokenList->passSymbol('=');
                    if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                        $alterOptions[Keyword::LOCK] = new AlterTableLock(AlterTableLock::DEFAULT);
                    } else {
                        $alterOptions[Keyword::LOCK] = $tokenList->expectNameOrStringEnum(AlterTableLock::class);
                    }
                    break;
                case Keyword::MODIFY:
                    // MODIFY [COLUMN] col_name column_definition [FIRST | AFTER col_name]
                    $tokenList->passKeyword(Keyword::COLUMN);
                    $column = $this->parseColumn($tokenList);
                    $after = null;
                    if ($tokenList->hasKeyword(Keyword::FIRST)) {
                        $after = ModifyColumnAction::FIRST;
                    } elseif ($tokenList->hasKeyword(Keyword::AFTER)) {
                        $after = $tokenList->expectName(EntityType::COLUMN);
                    }
                    $actions[] = new ModifyColumnAction($column, $after);
                    break;
                case Keyword::OPTIMIZE:
                    // OPTIMIZE PARTITION {partition_names | ALL}
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partitions = $this->parsePartitionNames($tokenList);
                    $actions[] = new OptimizePartitionAction($partitions);
                    break;
                case Keyword::ORDER:
                    // ORDER BY col_name [, col_name] ...
                    $tokenList->expectKeyword(Keyword::BY);
                    $columns = $this->expressionParser->parseOrderBy($tokenList, true);
                    $actions[] = new OrderByAction($columns);
                    break;
                case Keyword::REBUILD:
                    // REBUILD PARTITION {partition_names | ALL}
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partitions = $this->parsePartitionNames($tokenList);
                    $actions[] = new RebuildPartitionAction($partitions);
                    break;
                case Keyword::REMOVE:
                    // REMOVE PARTITIONING
                    $tokenList->expectKeyword(Keyword::PARTITIONING);
                    $actions[] = new RemovePartitioningAction();
                    break;
                case Keyword::RENAME:
                    if ($tokenList->hasKeyword(Keyword::COLUMN)) {
                        // RENAME COLUMN old_col_name TO new_col_name
                        $oldName = $tokenList->expectName(EntityType::COLUMN);
                        $tokenList->expectKeyword(Keyword::TO);
                        $newName = $tokenList->expectName(EntityType::COLUMN);
                        $actions[] = new RenameColumnAction($oldName, $newName);
                    } elseif ($tokenList->hasAnyKeyword(Keyword::INDEX, Keyword::KEY)) {
                        // RENAME {INDEX|KEY} old_index_name TO new_index_name
                        $oldName = $tokenList->expectName(EntityType::INDEX);
                        if (strtoupper($oldName) === Keyword::PRIMARY) {
                            throw new ParserException('Cannot rename key PRIMARY.', $tokenList);
                        }
                        $tokenList->expectKeyword(Keyword::TO);
                        $newName = $tokenList->expectName(EntityType::INDEX);
                        if ($newName === '' || strtoupper($newName) === Keyword::PRIMARY) {
                            throw new ParserException('Invalid index name.', $tokenList);
                        }
                        $actions[] = new RenameIndexAction($oldName, $newName);
                    } else {
                        // RENAME [TO|AS] new_tbl_name
                        $tokenList->getAnyKeyword(Keyword::TO, Keyword::AS);
                        $newName = $tokenList->expectObjectIdentifier();
                        $actions[] = new RenameToAction($newName);
                    }
                    break;
                case Keyword::REORGANIZE:
                    // REORGANIZE PARTITION [partition_names INTO (partition_definitions, ...)]
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $position = $tokenList->getPosition();
                    $oldPartitions = $newPartitions = null;
                    if ($tokenList->has(TokenType::NAME)) {
                        $oldPartitions = $this->parsePartitionNames($tokenList->rewind($position));
                        if ($oldPartitions === null) {
                            $tokenList->missing('Expected specific partition names, found "ALL".');
                        }
                        $tokenList->expectKeyword(Keyword::INTO);
                        $tokenList->expectSymbol('(');
                        $newPartitions = [];
                        do {
                            $newPartitions[] = $this->parsePartitionDefinition($tokenList);
                        } while ($tokenList->hasSymbol(','));
                        $tokenList->expectSymbol(')');
                    }
                    $actions[] = new ReorganizePartitionAction($oldPartitions, $newPartitions);
                    break;
                case Keyword::REPAIR:
                    // REPAIR PARTITION {partition_names | ALL}
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partitions = $this->parsePartitionNames($tokenList);
                    $actions[] = new RepairPartitionAction($partitions);
                    break;
                case Keyword::TRUNCATE:
                    // TRUNCATE PARTITION {partition_names | ALL}
                    $tokenList->expectKeyword(Keyword::PARTITION);
                    $partitions = $this->parsePartitionNames($tokenList);
                    $actions[] = new TruncatePartitionAction($partitions);
                    break;
                case Keyword::UPGRADE:
                    // UPGRADE PARTITIONING
                    $tokenList->expectKeyword(Keyword::PARTITIONING);
                    $actions[] = new UpgradePartitioningAction();
                    break;
                case Keyword::WITH:
                    // {WITHOUT|WITH} VALIDATION
                    $tokenList->expectKeyword(Keyword::VALIDATION);
                    $alterOptions[Keyword::VALIDATION] = true;
                    break;
                case Keyword::WITHOUT:
                    // {WITHOUT|WITH} VALIDATION
                    $tokenList->expectKeyword(Keyword::VALIDATION);
                    $alterOptions[Keyword::VALIDATION] = false;
                    break;
                case Keyword::PARTITION:
                    $tokenList->rewind(-1);
                    break;
                case null:
                    break;
                default:
                    [$option, $value] = $this->parseTableOption($tokenList->rewind($position), $tableOptions);
                    if ($option === null) {
                        $keywords = AlterTableActionType::getAllowedValues() + AlterTableOption::getAllowedValues()
                            + [Keyword::ALGORITHM, Keyword::LOCK, Keyword::WITH, Keyword::WITHOUT];
                        $tokenList->missingAnyKeyword(...array_values($keywords));
                    }
                    $tableOptions[$option] = $value;
                    $position = $tokenList->getPosition();
                    $trailingComma = $tokenList->hasSymbol(',');
                    do {
                        [$option, $value] = $this->parseTableOption($tokenList->rewind($position), $tableOptions);
                        if ($option === null) {
                            break;
                        }
                        $tableOptions[$option] = $value;
                        $position = $tokenList->getPosition();
                        $trailingComma = $tokenList->hasSymbol(',');
                    } while (true);
                    if ($trailingComma) {
                        $tokenList->rewind($position);
                    }
            }
        } while ($tokenList->hasSymbol(',') || $tokenList->seekKeyword(Keyword::REMOVE, 10));

        $partitioning = null;
        if ($tokenList->hasKeyword(Keyword::PARTITION)) {
            $partitioning = $this->parsePartitioning($tokenList->rewind(-1));
        }

        return new AlterTableCommand($name, $actions, $alterOptions, $tableOptions, $partitioning);
    }

    /**
     * CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
     *     (create_definition, ...)
     *     [table_options]
     *     [partition_options]
     *
     * CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
     *     [(create_definition, ...)]
     *     [table_options]
     *     [partition_options]
     *     [IGNORE | REPLACE]
     *     [AS] query_expression
     *
     * CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
     *     { LIKE old_tbl_name | (LIKE old_tbl_name) }
     *
     * query_expression:
     *     SELECT ...   (Some valid select or union statement)
     *
     * @return AnyCreateTableCommand&Statement
     */
    public function parseCreateTable(TokenList $tokenList): AnyCreateTableCommand
    {
        $tokenList->expectKeyword(Keyword::CREATE);
        $temporary = $tokenList->hasKeyword(Keyword::TEMPORARY);
        $tokenList->expectKeyword(Keyword::TABLE);
        $ifNotExists = $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);
        $table = $tokenList->expectObjectIdentifier();

        $position = $tokenList->getPosition();
        $bodyOpen = $tokenList->hasSymbol('(');
        if ($tokenList->hasKeyword(Keyword::LIKE)) {
            $oldTable = $tokenList->expectObjectIdentifier();
            if ($bodyOpen) {
                $tokenList->expectSymbol(')');
            }

            return new CreateTableLikeCommand($table, $oldTable, $temporary, $ifNotExists);
        }

        $query = $items = null;
        if ($bodyOpen) {
            if ($tokenList->hasAnyKeyword(Keyword::WITH, Keyword::SELECT)) {
                $tokenList->startSubquery(SubqueryType::CREATE_TABLE);
                $query = $this->queryParser->parseQuery($tokenList->rewind($position));
                $tokenList->endSubquery();
            } else {
                $items = $this->parseCreateTableBody($tokenList->rewind($position));
            }
        }

        $options = [];
        $trailingComma = false;
        $position = $tokenList->getPosition();
        do {
            [$option, $value] = $this->parseTableOption($tokenList, $options);
            if ($option === null) {
                break;
            }
            $options[$option] = $value;
            $position = $tokenList->getPosition();
            $trailingComma = $tokenList->hasSymbol(',');
        } while (true);
        if ($trailingComma) {
            $tokenList->rewind($position);
        }

        $partitioning = null;
        if ($tokenList->hasKeyword(Keyword::PARTITION)) {
            $partitioning = $this->parsePartitioning($tokenList->rewind(-1));
        }

        $duplicateOption = $tokenList->getKeywordEnum(DuplicateOption::class);

        $startTransaction = $tokenList->hasKeywords(Keyword::START, Keyword::TRANSACTION);

        if ($query === null) {
            if ($tokenList->hasKeyword(Keyword::AS) || $items === null || $duplicateOption !== null) {
                $tokenList->startSubquery(SubqueryType::CREATE_TABLE);
                $query = $this->queryParser->parseQuery($tokenList);
                $tokenList->endSubquery();
            } elseif (!$tokenList->isFinished()) {
                $position = $tokenList->getPosition();
                if (!$tokenList->hasSymbol(';')) {
                    $tokenList->startSubquery(SubqueryType::CREATE_TABLE);
                    $query = $this->queryParser->parseQuery($tokenList);
                    $tokenList->endSubquery();
                } else {
                    $tokenList->rewind($position);
                }
            }
        }

        if ($startTransaction) {
            if ($query !== null) {
                throw new ParserException('Cannot start transaction when creating table from a query.', $tokenList);
            }
            /** @var StorageEngine|null $engine */
            $engine = $options[TableOption::ENGINE] ?? null;
            if ($temporary || ($engine !== null && !$engine->transactional())) {
                throw new ParserException('Cannot start transaction when creating table with non-transactional engine.', $tokenList);
            }
        }

        return new CreateTableCommand($table, $items, $options, $partitioning, $temporary, $ifNotExists, $duplicateOption, $query, $startTransaction);
    }

    /**
     * (create_definition, ...)
     *
     * create_definition:
     *     col_name column_definition
     *   | [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name, ...) [index_option] ...
     *   | [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] (index_col_name, ...) [index_option] ...
     *   | {INDEX|KEY} [index_name] [index_type] (index_col_name, ...) [index_option] ...
     *   | {FULLTEXT|SPATIAL} [INDEX|KEY] [index_name] (index_col_name, ...) [index_option] ...
     *   | [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name, ...) reference_definition
     *   | check_constraint_definition
     *
     * @return non-empty-list<TableItem>
     */
    private function parseCreateTableBody(TokenList $tokenList): array
    {
        $items = [];
        $tokenList->expectSymbol('(');

        do {
            if ($tokenList->hasKeyword(Keyword::CHECK)) {
                $items[] = $this->parseCheck($tokenList);
            } elseif ($tokenList->hasAnyKeyword(Keyword::INDEX, Keyword::KEY, Keyword::FULLTEXT, Keyword::SPATIAL, Keyword::UNIQUE)) {
                $items[] = $this->parseIndex($tokenList->rewind(-1));
            } elseif ($tokenList->hasKeyword(Keyword::PRIMARY)) {
                $items[] = $this->parseIndex($tokenList, true);
            } elseif ($tokenList->hasKeyword(Keyword::FOREIGN)) {
                $items[] = $this->parseForeignKey($tokenList->rewind(-1));
            } elseif ($tokenList->hasKeyword(Keyword::CONSTRAINT)) {
                $items[] = $this->parseConstraint($tokenList->rewind(-1));
            } else {
                $items[] = $this->parseColumn($tokenList);
            }
        } while ($tokenList->hasSymbol(','));

        $tokenList->expectSymbol(')');

        return $items;
    }

    /**
     * create_definition:
     *     col_name column_definition
     *
     * column_definition:
     *     ordinary_column_definition
     *   | generated_column_definition
     */
    private function parseColumn(TokenList $tokenList): ColumnDefinition
    {
        $name = $tokenList->expectNonReservedName(EntityType::COLUMN);

        $type = $this->expressionParser->parseColumnType($tokenList);
        if ($tokenList->getKeywordEnum(BaseType::class) !== null) {
            // column type may appear more than once time. last one wins.
            // todo: this is not represented and can not be analyzed by rules later. add parser warnings?
            $type = $this->expressionParser->parseColumnType($tokenList->rewind(-1));
        }

        $keyword = $tokenList->getAnyKeyword(Keyword::GENERATED, Keyword::AS);
        if ($keyword === null) {
            return $this->parseOrdinaryColumn($name, $type, $tokenList);
        } else {
            if ($keyword === Keyword::GENERATED) {
                $tokenList->expectKeywords(Keyword::ALWAYS, Keyword::AS);
            }

            return $this->parseGeneratedColumn($name, $type, $tokenList);
        }
    }

    /**
     * ordinary_column_definition:
     *     data_type [NOT NULL | NULL] [DEFAULT default_value]
     *       [COLLATE collation_name]
     *       [VISIBLE | INVISIBLE]
     *       [AUTO_INCREMENT] [UNIQUE [KEY]] [[PRIMARY] KEY]
     *       [COMMENT 'string']
     *       [COLUMN_FORMAT {FIXED|DYNAMIC|DEFAULT}]
     *       [ENGINE_ATTRIBUTE [=] 'string']
     *       [SECONDARY_ENGINE_ATTRIBUTE [=] 'string']
     *       [STORAGE {DISK | MEMORY}]
     *       [ON UPDATE CURRENT_TIMESTAMP[(...)]] -- not documented in main article: @see https://dev.mysql.com/doc/refman/8.0/en/timestamp-initialization.html
     *       [reference_definition]
     *       [check_constraint_definition]
     */
    private function parseOrdinaryColumn(string $name, ColumnType $type, TokenList $tokenList): ColumnDefinition
    {
        $checks = [];
        $null = $default = $index = $comment = $columnFormat = $reference = $onUpdate = $visible = null;
        $engineAttribute = $secondaryEngineAttribute = $storage = null;
        $autoIncrement = false;
        $keywords = [
            Keyword::AUTO_INCREMENT, Keyword::CHARACTER, Keyword::CHAR, Keyword::CHARSET, Keyword::CHECK, Keyword::COLLATE,
            Keyword::COLUMN_FORMAT, Keyword::COMMENT, Keyword::CONSTRAINT, Keyword::DEFAULT, Keyword::ENGINE_ATTRIBUTE,
            Keyword::INVISIBLE, Keyword::KEY, Keyword::NOT, Keyword::NULL, Keyword::ON, Keyword::PRIMARY, Keyword::REFERENCES,
            Keyword::SECONDARY_ENGINE_ATTRIBUTE, Keyword::SRID, Keyword::STORAGE, Keyword::UNIQUE, Keyword::VISIBLE,
        ];
        while (($keyword = $tokenList->getAnyKeyword(...$keywords)) !== null) {
            switch ($keyword) {
                case Keyword::NOT:
                    // todo: ignoring HeatWave secondary engine feature
                    if ($tokenList->hasKeyword(Keyword::SECONDARY)) {
                        break;
                    }
                    // [NOT NULL | NULL]
                    $tokenList->expectKeyword(Keyword::NULL);
                    $null = false;
                    break;
                case Keyword::NULL:
                    $null = true;
                    break;
                case Keyword::DEFAULT:
                    if ($tokenList->hasSymbol('(')) {
                        // [DEFAULT (expr)]
                        $default = new Parentheses($this->expressionParser->parseExpression($tokenList));
                        $tokenList->expectSymbol(')');
                        break;
                    }
                    $function = $tokenList->getAnyName(BuiltInFunction::CURRENT_TIMESTAMP, BuiltInFunction::NOW);
                    if ($function === null) {
                        // [DEFAULT default_value]
                        $default = $this->expressionParser->parseLiteral($tokenList);
                        break;
                    }
                    // [DEFAULT CURRENT_TIMESTAMP[(...)]]
                    $function = new BuiltInFunction($function);
                    if ($tokenList->hasSymbol('(')) {
                        $param = $tokenList->getUnsignedInt();
                        $params = $param !== null ? [new UintLiteral($param)] : [];
                        $tokenList->expectSymbol(')');
                        $default = new FunctionCall($function, $params);
                    } elseif (!$function->isBare()) {
                        // throws
                        $tokenList->expectSymbol('(');
                        $tokenList->expectSymbol(')');
                        $default = new FunctionCall($function);
                    } else {
                        $default = new FunctionCall($function);
                    }
                    break;
                case Keyword::VISIBLE:
                    // [VISIBLE | INVISIBLE]
                    $tokenList->check('column visibility', 80023);
                    $visible = true;
                    break;
                case Keyword::INVISIBLE:
                    // [VISIBLE | INVISIBLE]
                    $tokenList->check('column visibility', 80023);
                    $visible = false;
                    break;
                case Keyword::AUTO_INCREMENT:
                    // [AUTO_INCREMENT]
                    $autoIncrement = true;
                    break;
                case Keyword::ON:
                    // [ON UPDATE CURRENT_TIMESTAMP[(...)]]
                    $tokenList->expectKeyword(Keyword::UPDATE);
                    $tokenList->expectAnyName(BuiltInFunction::CURRENT_TIMESTAMP, BuiltInFunction::NOW);
                    if ($tokenList->hasSymbol('(')) {
                        $param = $tokenList->getUnsignedInt();
                        $params = $param !== null ? [new UintLiteral($param)] : [];
                        $tokenList->expectSymbol(')');
                        $onUpdate = new FunctionCall(new BuiltInFunction(BuiltInFunction::CURRENT_TIMESTAMP), $params);
                    } else {
                        $onUpdate = new FunctionCall(new BuiltInFunction(BuiltInFunction::CURRENT_TIMESTAMP));
                    }
                    break;
                case Keyword::UNIQUE:
                    // [UNIQUE [KEY] | [PRIMARY] KEY]
                    $tokenList->passKeyword(Keyword::KEY);
                    $index = new IndexType(IndexType::UNIQUE);
                    break;
                case Keyword::PRIMARY:
                    $tokenList->expectKeyword(Keyword::KEY);
                    $index = new IndexType(IndexType::PRIMARY);
                    break;
                case Keyword::KEY:
                    $index = new IndexType(IndexType::INDEX);
                    break;
                case Keyword::COMMENT:
                    // [COMMENT 'string']
                    $comment = $tokenList->expectString();
                    $limit = $tokenList->getSession()->getPlatform()->getMaxLengths()[EntityType::FIELD_COMMENT];
                    if (strlen($comment) > $limit && $tokenList->getSession()->getMode()->containsAny(SqlMode::STRICT_ALL_TABLES)) {
                        throw new ParserException("Column comment length exceeds limit of {$limit} bytes.", $tokenList);
                    }
                    break;
                case Keyword::COLUMN_FORMAT:
                    // [COLUMN_FORMAT {FIXED|DYNAMIC|DEFAULT}]
                    $columnFormat = $tokenList->expectKeywordEnum(ColumnFormat::class);
                    break;
                case Keyword::ENGINE_ATTRIBUTE:
                    // [ENGINE_ATTRIBUTE [=] 'string']
                    $tokenList->passSymbol('=');
                    $engineAttribute = $tokenList->expectString();
                    break;
                case Keyword::SECONDARY_ENGINE_ATTRIBUTE:
                    // [SECONDARY_ENGINE_ATTRIBUTE [=] 'string']
                    $tokenList->passSymbol('=');
                    $secondaryEngineAttribute = $tokenList->expectString();
                    break;
                case Keyword::STORAGE:
                    // [STORAGE {DISK | MEMORY | DEFAULT}]
                    $storage = $tokenList->expectKeywordEnum(StorageType::class);
                    break;
                case Keyword::REFERENCES:
                    // [reference_definition]
                    $reference = $this->parseReference($tokenList->rewind(-1));
                    break;
                case Keyword::CONSTRAINT:
                    // [check_constraint_definition]
                    $checks[] = $this->parseConstraint($tokenList->rewind(-1));
                    break;
                case Keyword::CHECK:
                    // [check_constraint_definition]
                    $checks[] = $this->parseCheck($tokenList);
                    break;
                case Keyword::CHARACTER:
                case Keyword::CHAR:
                    $tokenList->expectKeyword(Keyword::SET);
                case Keyword::CHARSET:
                    $type = $type->addCharset($tokenList->expectCharsetName());
                    break;
                case Keyword::COLLATE:
                    $type = $type->addCollation($tokenList->expectCollationName());
                    break;
                case Keyword::SRID:
                    $type = $type->addSrid((int) $tokenList->expectUnsignedInt());
                    break;
            }
        }
        if ($checks === []) {
            $checks = null;
        }

        $hasDefaultValue = $default !== null && !$default instanceof NullLiteral && !$default instanceof Parentheses;
        if ($hasDefaultValue && $type->getBaseType()->isSpatial()) {
            throw new ParserException('GEOMETRY columns cannot have a default value.', $tokenList);
        }

        // default '' is allowed in non-strict mode
        $isEmptyStringAndNonStrict = $default instanceof StringLiteral && $default->getValue() === ''
            && !$tokenList->getSession()->getMode()->containsAny(SqlMode::STRICT_TRANS_TABLES);
        if ($hasDefaultValue && $type->getBaseType()->isBlob() && !$isEmptyStringAndNonStrict) {
            throw new ParserException('BLOB columns cannot have a default value.', $tokenList);
        }

        return new ColumnDefinition($name, $type, $default, $null, $visible, $autoIncrement, $onUpdate, $comment, $index, $columnFormat, $engineAttribute, $secondaryEngineAttribute, $storage, $reference, $checks);
    }

    /**
     * generated_column_definition:
     *     data_type [NOT NULL | NULL]
     *       [COLLATE collation_name]
     *       [GENERATED ALWAYS] AS (expression)
     *       [VIRTUAL | STORED]
     *       [VISIBLE | INVISIBLE]
     *       [UNIQUE [KEY]] [[PRIMARY] KEY]
     *       [COMMENT comment]
     *       [reference_definition]
     *       [check_constraint_definition]
     */
    private function parseGeneratedColumn(string $name, ColumnType $type, TokenList $tokenList): ColumnDefinition
    {
        $tokenList->expectSymbol('(');
        $expression = $this->expressionParser->parseExpression($tokenList);
        $tokenList->expectSymbol(')');

        $checks = [];
        $null = $index = $comment = $generatedType = $reference = $visible = null;
        $keywords = [
            Keyword::CHARACTER, Keyword::CHAR, Keyword::CHARSET, Keyword::CHECK, Keyword::COLLATE, Keyword::COMMENT,
            Keyword::INVISIBLE, Keyword::KEY, Keyword::NOT, Keyword::NULL, Keyword::PRIMARY, Keyword::REFERENCES,
            Keyword::SRID, Keyword::STORED, Keyword::UNIQUE, Keyword::VIRTUAL, Keyword::VISIBLE,
        ];
        while (($keyword = $tokenList->getAnyKeyword(...$keywords)) !== null) {
            switch ($keyword) {
                case Keyword::NOT:
                    // todo: ignoring HeatWave secondary engine feature
                    if ($tokenList->hasKeyword(Keyword::SECONDARY)) {
                        break;
                    }
                    // [NOT NULL | NULL]
                    $tokenList->expectKeyword(Keyword::NULL);
                    $null = false;
                    break;
                case Keyword::NULL:
                    $null = true;
                    break;
                case Keyword::VIRTUAL:
                    // [VIRTUAL | STORED]
                    $generatedType = new GeneratedColumnType(GeneratedColumnType::VIRTUAL);
                    break;
                case Keyword::STORED:
                    // [VIRTUAL | STORED]
                    $generatedType = new GeneratedColumnType(GeneratedColumnType::STORED);
                    break;
                case Keyword::VISIBLE:
                    // [VISIBLE | INVISIBLE]
                    $tokenList->check('column visibility', 80023);
                    $visible = true;
                    break;
                case Keyword::INVISIBLE:
                    // [VISIBLE | INVISIBLE]
                    $tokenList->check('column visibility', 80023);
                    $visible = false;
                    break;
                case Keyword::UNIQUE:
                    // [UNIQUE [KEY] | [PRIMARY] KEY]
                    $tokenList->passKeyword(Keyword::KEY);
                    $index = new IndexType(IndexType::UNIQUE);
                    break;
                case Keyword::PRIMARY:
                    $tokenList->expectKeyword(Keyword::KEY);
                    $index = new IndexType(IndexType::PRIMARY);
                    break;
                case Keyword::KEY:
                    $index = new IndexType(IndexType::INDEX);
                    break;
                case Keyword::COMMENT:
                    // [COMMENT 'string']
                    $comment = $tokenList->expectString();
                    $limit = $tokenList->getSession()->getPlatform()->getMaxLengths()[EntityType::FIELD_COMMENT];
                    if (strlen($comment) > $limit && $tokenList->getSession()->getMode()->containsAny(SqlMode::STRICT_ALL_TABLES)) {
                        throw new ParserException("Column comment length exceeds limit of {$limit} bytes.", $tokenList);
                    }
                    break;
                case Keyword::REFERENCES:
                    // [reference_definition]
                    $reference = $this->parseReference($tokenList->rewind(-1));
                    break;
                case Keyword::CHECK:
                    // [check_constraint_definition]
                    $checks[] = $this->parseCheck($tokenList);
                    break;
                case Keyword::CHARACTER:
                case Keyword::CHAR:
                    $tokenList->expectKeyword(Keyword::SET);
                case Keyword::CHARSET:
                    $type = $type->addCharset($tokenList->expectCharsetName());
                    break;
                case Keyword::COLLATE:
                    if ($type->getCollation() !== null) {
                        throw new ParserException('Duplicit collation definition on column.', $tokenList);
                    }
                    $type = $type->addCollation($tokenList->expectCollationName());
                    break;
                case Keyword::SRID:
                    $type = $type->addSrid((int) $tokenList->expectUnsignedInt());
                    break;
            }
        }
        if ($checks === []) {
            $checks = null;
        }

        return ColumnDefinition::createGenerated($name, $type, $expression, $generatedType, $null, $visible, $comment, $index, $reference, $checks);
    }

    /**
     * check_constraint_definition:
     *     [CONSTRAINT [symbol]] CHECK (expr) [[NOT] ENFORCED]
     */
    private function parseCheck(TokenList $tokenList): CheckDefinition
    {
        $tokenList->expectSymbol('(');
        $expression = $this->expressionParser->parseExpression($tokenList);
        $tokenList->expectSymbol(')');

        $enforced = null;
        if ($tokenList->hasKeyword(Keyword::ENFORCED)) {
            $enforced = true;
        } elseif ($tokenList->hasKeywords(Keyword::NOT, Keyword::ENFORCED)) {
            $enforced = false;
        }

        return new CheckDefinition($expression, $enforced);
    }

    /**
     * create_definition:
     *   | [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name, ...) [index_option] ...
     *   | [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] (index_col_name, ...) [index_option] ...
     *   | {INDEX|KEY} [index_name] [index_type] (index_col_name, ...) [index_option] ...
     *   | {FULLTEXT|SPATIAL} [INDEX|KEY] [index_name] (index_col_name, ...) [index_option] ...
     */
    private function parseIndex(TokenList $tokenList, bool $primary = false): IndexDefinition
    {
        if ($primary) {
            $index = $this->indexCommandsParser->parseIndexDefinition($tokenList, true);

            return $index->duplicateAsPrimary();
        } else {
            return $this->indexCommandsParser->parseIndexDefinition($tokenList, true);
        }
    }

    /**
     * create_definition:
     *   | [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (index_col_name, ...) [index_option] ...
     *   | [CONSTRAINT [symbol]] UNIQUE [INDEX|KEY] [index_name] [index_type] (index_col_name, ...) [index_option] ...
     *   | [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (index_col_name, ...) reference_definition
     *   | [CONSTRAINT [symbol]] CHECK (expr) [[NOT] ENFORCED]
     */
    private function parseConstraint(TokenList $tokenList): ConstraintDefinition
    {
        $name = null;
        if ($tokenList->hasKeyword(Keyword::CONSTRAINT)) {
            $name = $tokenList->getNonReservedName(EntityType::CONSTRAINT);
        }

        $keyword = $tokenList->expectAnyKeyword(Keyword::PRIMARY, Keyword::UNIQUE, Keyword::FOREIGN, Keyword::CHECK);
        if ($keyword === Keyword::PRIMARY) {
            $type = new ConstraintType(ConstraintType::PRIMARY_KEY);
            $body = $this->parseIndex($tokenList, true);

            return new ConstraintDefinition($type, $name, $body);
        } elseif ($keyword === Keyword::UNIQUE) {
            $type = new ConstraintType(ConstraintType::UNIQUE_KEY);
            $body = $this->parseIndex($tokenList->rewind(-1));

            return new ConstraintDefinition($type, $name, $body);
        } elseif ($keyword === Keyword::FOREIGN) {
            $type = new ConstraintType(ConstraintType::FOREIGN_KEY);
            $body = $this->parseForeignKey($tokenList->rewind(-1));

            return new ConstraintDefinition($type, $name, $body);
        } else {
            $type = new ConstraintType(ConstraintType::CHECK);
            $body = $this->parseCheck($tokenList);

            return new ConstraintDefinition($type, $name, $body);
        }
    }

    /**
     * create_definition:
     *     [CONSTRAINT [symbol]] FOREIGN KEY
     *         [index_name] (index_col_name, ...) reference_definition
     */
    private function parseForeignKey(TokenList $tokenList): ForeignKeyDefinition
    {
        $tokenList->expectKeywords(Keyword::FOREIGN, Keyword::KEY);
        $indexName = $tokenList->getName(EntityType::INDEX);

        $columns = $this->parseNonEmptyColumnList($tokenList);
        $reference = $this->parseReference($tokenList);

        try {
            return new ForeignKeyDefinition($columns, $reference, $indexName);
        } catch (InvalidDefinitionException $e) {
            throw new ParserException($e->getMessage(), $tokenList, $e);
        }
    }

    /**
     * reference_definition:
     *     REFERENCES tbl_name (index_col_name, ...)
     *     [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
     *     [ON DELETE reference_option]
     *     [ON UPDATE reference_option]
     *
     * reference_option:
     *     RESTRICT | CASCADE | SET NULL | NO ACTION | SET DEFAULT
     */
    private function parseReference(TokenList $tokenList): ReferenceDefinition
    {
        $tokenList->expectKeyword(Keyword::REFERENCES);
        $table = $tokenList->expectObjectIdentifier();

        $columns = $this->parseNonEmptyColumnList($tokenList);

        $matchType = null;
        if ($tokenList->hasKeyword(Keyword::MATCH)) {
            $matchType = $tokenList->expectKeywordEnum(ForeignKeyMatchType::class);
        }

        $onDelete = $onUpdate = null;
        if ($tokenList->hasKeywords(Keyword::ON, Keyword::DELETE)) {
            $onDelete = $tokenList->expectMultiKeywordsEnum(ForeignKeyAction::class);
        }
        if ($tokenList->hasKeywords(Keyword::ON, Keyword::UPDATE)) {
            $onUpdate = $tokenList->expectMultiKeywordsEnum(ForeignKeyAction::class);
        }
        // order of DELETE/UPDATE may be switched
        if ($onDelete === null && $tokenList->hasKeywords(Keyword::ON, Keyword::DELETE)) {
            $onDelete = $tokenList->expectMultiKeywordsEnum(ForeignKeyAction::class);
        }

        return new ReferenceDefinition($table, $columns, $onDelete, $onUpdate, $matchType);
    }

    /**
     * table_option:
     *     AUTOEXTEND_SIZE [=] value
     *   | AUTO_INCREMENT [=] value
     *   | AVG_ROW_LENGTH [=] value
     *   | [DEFAULT] CHARACTER SET [=] charset_name
     *   | CHECKSUM [=] {0 | 1}
     *   | [DEFAULT] COLLATE [=] collation_name
     *   | COMMENT [=] 'string'
     *   | COMPRESSION [=] {'ZLIB'|'LZ4'|'NONE'}
     *   | CONNECTION [=] 'connect_string'
     *   | DATA DIRECTORY [=] 'absolute path to directory'
     *   | DELAY_KEY_WRITE [=] {0 | 1}
     *   | ENCRYPTION [=] {'Y' | 'N'}
     *   | ENGINE [=] engine_name
     *   | INDEX DIRECTORY [=] 'absolute path to directory'
     *   | INSERT_METHOD [=] { NO | FIRST | LAST }
     *   | KEY_BLOCK_SIZE [=] value
     *   | MAX_ROWS [=] value
     *   | MIN_ROWS [=] value
     *   | PACK_KEYS [=] {0 | 1 | DEFAULT}
     *   | PASSWORD [=] 'string'
     *   | ROW_FORMAT [=] {DEFAULT|DYNAMIC|FIXED|COMPRESSED|REDUNDANT|COMPACT}
     *   | STORAGE [=] {DISK | MEMORY}
     *   | STATS_AUTO_RECALC [=] {DEFAULT|0|1}
     *   | STATS_PERSISTENT [=] {DEFAULT|0|1}
     *   | STATS_SAMPLE_PAGES [=] value
     *   | TABLESPACE tablespace_name
     *   | UNION [=] (tbl_name[,tbl_name]...)
     *
     * @param array<TableOption::*, TableOptionValue> $options
     * @return array{TableOption::*|null, TableOptionValue}
     */
    private function parseTableOption(TokenList $tokenList, array $options): array
    {
        $position = $tokenList->getPosition();
        $keyword = $tokenList->getKeyword();
        if ($keyword === null) {
            return [null, 0];
        }

        switch ($keyword) {
            case Keyword::AUTOEXTEND_SIZE:
                $tokenList->passSymbol('=');

                return [TableOption::AUTOEXTEND_SIZE, $tokenList->expectSize()];
            case Keyword::AUTO_INCREMENT:
                $tokenList->passSymbol('=');

                return [TableOption::AUTO_INCREMENT, $tokenList->expectUnsignedInt()];
            case Keyword::AVG_ROW_LENGTH:
                $tokenList->passSymbol('=');
                $length = (int) $tokenList->expectUnsignedInt();
                if ($length >= 2 ** 32) {
                    throw new ParserException('Invalid average row length value.', $tokenList);
                }

                return [TableOption::AVG_ROW_LENGTH, $length];
            case Keyword::CHARACTER:
            case Keyword::CHAR:
                $tokenList->expectKeyword(Keyword::SET);
                // fall-through
            case Keyword::CHARSET:
                $tokenList->passSymbol('=');

                $charset = $tokenList->expectCharsetName();
                if (isset($options[TableOption::CHARACTER_SET])) {
                    // charset can be specified twice (not represented in model)
                    // todo: parser warning
                    /** @var Charset $previousCharset */
                    $previousCharset = $options[TableOption::CHARACTER_SET];
                    if (!$previousCharset->equals($charset)) {
                        throw new ParserException('Charset declaration conflict.', $tokenList);
                    }
                }

                return [TableOption::CHARACTER_SET, $charset];
            case Keyword::CHECKSUM:
                $tokenList->passSymbol('=');

                return [TableOption::CHECKSUM, $tokenList->expectBool()];
            case Keyword::COLLATE:
                $tokenList->passSymbol('=');

                return [TableOption::COLLATE, $tokenList->expectCollationName()];
            case Keyword::COMMENT:
                $tokenList->passSymbol('=');
                $comment = $tokenList->expectString();
                $limit = $tokenList->getSession()->getPlatform()->getMaxLengths()[EntityType::TABLE_COMMENT];
                if (strlen($comment) > $limit && $tokenList->getSession()->getMode()->containsAny(SqlMode::STRICT_ALL_TABLES)) {
                    throw new ParserException("Table comment length exceeds limit of {$limit} bytes.", $tokenList);
                }

                return [TableOption::COMMENT, $comment];
            case Keyword::COMPRESSION:
                $tokenList->passSymbol('=');
                $compression = $tokenList->expectString();
                try {
                    $compression = new TableCompression($compression === '' ? TableCompression::NONE : $compression);
                } catch (InvalidEnumValueException $e) {
                    throw new InvalidValueException('TableCompression', $tokenList, $e);
                }

                return [TableOption::COMPRESSION, $compression];
            case Keyword::CONNECTION:
                $tokenList->passSymbol('=');

                return [TableOption::CONNECTION, $tokenList->expectString()];
            case Keyword::DATA:
                $tokenList->expectKeyword(Keyword::DIRECTORY);
                $tokenList->passSymbol('=');
                $value = $tokenList->expectString();
                if ($value === '' || $value === '.') {
                    throw new ParserException('Value of DATA DIRECTORY can not be empty.', $tokenList);
                }

                return [TableOption::DATA_DIRECTORY, $value];
            case Keyword::DEFAULT:
                if ($tokenList->hasKeyword(Keyword::CHARSET)) {
                    $tokenList->passSymbol('=');

                    return [TableOption::CHARACTER_SET, $tokenList->expectCharsetName()];
                } elseif ($tokenList->hasKeyword(Keyword::CHARACTER)) {
                    $tokenList->expectKeyword(Keyword::SET);
                    $tokenList->passSymbol('=');

                    return [TableOption::CHARACTER_SET, $tokenList->expectCharsetName()];
                } else {
                    $tokenList->expectKeyword(Keyword::COLLATE);
                    $tokenList->passSymbol('=');

                    return [TableOption::COLLATE, $tokenList->expectCollationName()];
                }
            case Keyword::DELAY_KEY_WRITE:
                $tokenList->passSymbol('=');

                return [TableOption::DELAY_KEY_WRITE, $tokenList->expectBool()];
            case Keyword::ENCRYPTION:
                $tokenList->passSymbol('=');

                return [TableOption::ENCRYPTION, $tokenList->expectBool()];
            case Keyword::ENGINE:
                $tokenList->passSymbol('=');

                return [TableOption::ENGINE, $tokenList->expectStorageEngineName()];
            case Keyword::SECONDARY_ENGINE:
                $tokenList->passSymbol('=');

                return [TableOption::SECONDARY_ENGINE, $tokenList->expectStorageEngineName()];
            case Keyword::INDEX:
                $tokenList->expectKeyword(Keyword::DIRECTORY);
                $tokenList->passSymbol('=');

                return [TableOption::INDEX_DIRECTORY, $tokenList->expectString()];
            case Keyword::INSERT_METHOD:
                $tokenList->passSymbol('=');

                return [TableOption::INSERT_METHOD, $tokenList->expectKeywordEnum(TableInsertMethod::class)];
            case Keyword::KEY_BLOCK_SIZE:
                $tokenList->passSymbol('=');
                $size = (int) $tokenList->expectUnsignedInt();
                if ($size >= 2 ** 31 - 1) {
                    throw new ParserException('Invalid key block size.', $tokenList);
                }

                return [TableOption::KEY_BLOCK_SIZE, $size];
            case Keyword::MAX_ROWS:
                $tokenList->passSymbol('=');

                return [TableOption::MAX_ROWS, (int) $tokenList->expectUnsignedInt()];
            case Keyword::MIN_ROWS:
                $tokenList->passSymbol('=');

                return [TableOption::MIN_ROWS, (int) $tokenList->expectUnsignedInt()];
            case Keyword::PACK_KEYS:
                $tokenList->passSymbol('=');
                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    return [TableOption::PACK_KEYS, new ThreeStateValue(ThreeStateValue::DEFAULT)];
                } else {
                    return [TableOption::PACK_KEYS, new ThreeStateValue($tokenList->expectUnsignedInt())];
                }
            case Keyword::PASSWORD:
                $tokenList->passSymbol('=');

                return [TableOption::PASSWORD, $tokenList->expectString()];
            case Keyword::ROW_FORMAT:
                $tokenList->passSymbol('=');

                return [TableOption::ROW_FORMAT, $tokenList->expectKeywordEnum(TableRowFormat::class)];
            case Keyword::STORAGE:
                $tokenList->passSymbol('=');

                return [TableOption::STORAGE, $tokenList->expectKeywordEnum(StorageType::class)];
            case Keyword::STATS_AUTO_RECALC:
                $tokenList->passSymbol('=');
                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    return [TableOption::STATS_AUTO_RECALC, new ThreeStateValue(ThreeStateValue::DEFAULT)];
                } else {
                    return [TableOption::STATS_AUTO_RECALC, new ThreeStateValue($tokenList->expectUnsignedInt())];
                }
            case Keyword::STATS_PERSISTENT:
                $tokenList->passSymbol('=');
                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    return [TableOption::STATS_PERSISTENT, new ThreeStateValue(ThreeStateValue::DEFAULT)];
                } else {
                    return [TableOption::STATS_PERSISTENT, new ThreeStateValue($tokenList->expectUnsignedInt())];
                }
            case Keyword::STATS_SAMPLE_PAGES:
                $tokenList->passSymbol('=');

                if ($tokenList->hasKeyword(Keyword::DEFAULT)) {
                    return [TableOption::STATS_SAMPLE_PAGES, new DefaultLiteral()];
                } else {
                    $value = (int) $tokenList->expectUnsignedInt();
                    if ($value === 0 || $value >= PowersOfTwo::_64K) {
                        throw new ParserException('The valid range for stats_sample_pages is 1 to 65535.', $tokenList);
                    }
                    return [TableOption::STATS_SAMPLE_PAGES, $value];
                }
            case Keyword::TABLESPACE:
                $tokenList->passSymbol('=');

                return [TableOption::TABLESPACE, $tokenList->expectNonReservedName(EntityType::TABLESPACE)];
            case Keyword::UNION:
                $tokenList->passSymbol('=');
                $tokenList->expectSymbol('(');
                $tables = [];
                if (!$tokenList->hasSymbol(')')) {
                    do {
                        $tables[] = $tokenList->expectObjectIdentifier();
                    } while ($tokenList->hasSymbol(','));
                    $tokenList->expectSymbol(')');
                }

                return [TableOption::UNION, $tables];
            default:
                $tokenList->rewind($position);

                return [null, 0];
        }
    }

    /**
     * partition_options:
     *     PARTITION BY
     *         { [LINEAR] HASH(expr)
     *         | [LINEAR] KEY [ALGORITHM={1|2}] (column_list)
     *         | RANGE{(expr) | COLUMNS(column_list)}
     *         | LIST{(expr) | COLUMNS(column_list)}
     *         }
     *     [PARTITIONS num]
     *     [SUBPARTITION BY
     *         { [LINEAR] HASH(expr)
     *         | [LINEAR] KEY [ALGORITHM={1|2}] (column_list) }
     *         [SUBPARTITIONS num]
     *     ]
     *     [(partition_definition [, partition_definition] ...)]
     */
    private function parsePartitioning(TokenList $tokenList): PartitioningDefinition
    {
        $tokenList->expectKeywords(Keyword::PARTITION, Keyword::BY);
        $condition = $this->parsePartitionCondition($tokenList);

        $partitionsNumber = null;
        if ($tokenList->hasKeyword(Keyword::PARTITIONS)) {
            $partitionsNumber = (int) $tokenList->expectUnsignedInt();
        }
        $subpartitionsCondition = $subpartitionsNumber = null;
        if ($tokenList->hasKeywords(Keyword::SUBPARTITION, Keyword::BY)) {
            $subpartitionsCondition = $this->parsePartitionCondition($tokenList, true);
            if ($tokenList->hasKeyword(Keyword::SUBPARTITIONS)) {
                $subpartitionsNumber = (int) $tokenList->expectUnsignedInt();
            }
        }

        $partitions = null;
        if ($tokenList->hasSymbol('(')) {
            $partitions = [];
            $subCount = false;
            do {
                $partition = $this->parsePartitionDefinition($tokenList, $condition);
                $partitions[] = $partition;

                // every partition has the same number of subpartitions
                if ($subCount === false) {
                    $sub = $partition->getSubpartitions();
                    $subCount = $count = $sub === null ? 0 : count($sub);
                } else {
                    $sub = $partition->getSubpartitions();
                    $count = $sub === null ? 0 : count($sub);
                    if ($subCount !== $count) {
                        throw new ParserException('Uneven number of subpartitions.', $tokenList);
                    }
                }
                if ($subpartitionsCondition === null && $count !== 0) {
                    throw new ParserException('Subpartitions without SUBPARTITION clause.', $tokenList);
                }
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');

            if ($partitionsNumber !== null && $partitionsNumber !== count($partitions)) {
                throw new ParserException('Wrong number of partitions.', $tokenList);
            }
            if ($subpartitionsNumber !== null) {
                foreach ($partitions as $part) {
                    $subpartitions = $part->getSubpartitions();
                    if ($subpartitions !== null && count($subpartitions) !== $subpartitionsNumber) {
                        throw new ParserException('Wrong number of subpartitions.', $tokenList);
                    }
                }
            }
        }

        return new PartitioningDefinition($condition, $partitions, $partitionsNumber, $subpartitionsCondition, $subpartitionsNumber);
    }

    /**
     * condition:
     *     [LINEAR] HASH(expr)
     *   | [LINEAR] KEY [ALGORITHM={1|2}] (column_list)
     *   | RANGE{(expr) | COLUMNS(column_list)}
     *   | LIST{(expr) | COLUMNS(column_list)}
     */
    private function parsePartitionCondition(TokenList $tokenList, bool $subpartition = false): PartitioningCondition
    {
        $linear = $tokenList->hasKeyword(Keyword::LINEAR);
        if ($linear || $subpartition) {
            $keywords = [Keyword::HASH, Keyword::KEY];
        } else {
            $keywords = [Keyword::HASH, Keyword::KEY, Keyword::RANGE, Keyword::LIST];
        }
        $keyword = $tokenList->expectAnyKeyword(...$keywords);
        if ($keyword === Keyword::HASH) {
            $tokenList->expectSymbol('(');
            $expression = $this->expressionParser->parseExpression($tokenList);
            $tokenList->expectSymbol(')');
            $type = new PartitioningConditionType($linear ? PartitioningConditionType::LINEAR_HASH : PartitioningConditionType::HASH);
            $condition = new PartitioningCondition($type, $expression);
        } elseif ($keyword === Keyword::KEY) {
            $algorithm = null;
            if ($tokenList->hasKeyword(Keyword::ALGORITHM)) {
                $tokenList->expectOperator(Operator::EQUAL);
                $algorithm = (int) $tokenList->expectUnsignedInt();
                if ($algorithm !== 1 && $algorithm !== 2) {
                    throw new ParserException('Only 1|2 values allowed for partitioning algorithm.', $tokenList);
                }
            }
            $columns = $this->parseColumnList($tokenList);
            $type = new PartitioningConditionType($linear ? PartitioningConditionType::LINEAR_KEY : PartitioningConditionType::KEY);
            $condition = new PartitioningCondition($type, null, $columns, $algorithm);
        } elseif ($keyword === Keyword::RANGE) {
            $type = new PartitioningConditionType(PartitioningConditionType::RANGE);
            if ($tokenList->hasKeyword(Keyword::COLUMNS)) {
                $columns = $this->parseNonEmptyColumnList($tokenList);
                $condition = new PartitioningCondition($type, null, $columns);
            } else {
                $tokenList->expectSymbol('(');
                $expression = $this->expressionParser->parseExpression($tokenList);
                $tokenList->expectSymbol(')');
                $condition = new PartitioningCondition($type, $expression);
            }
        } else {
            $type = new PartitioningConditionType(PartitioningConditionType::LIST);
            if ($tokenList->hasKeyword(Keyword::COLUMNS)) {
                $columns = $this->parseNonEmptyColumnList($tokenList);
                $condition = new PartitioningCondition($type, null, $columns);
            } else {
                $tokenList->expectSymbol('(');
                $expression = $this->expressionParser->parseExpression($tokenList);
                $tokenList->expectSymbol(')');
                $condition = new PartitioningCondition($type, $expression);
            }
        }

        return $condition;
    }

    /**
     * partition_definition:
     *     PARTITION partition_name
     *         [VALUES
     *             {LESS THAN {(expr | value_list) | MAXVALUE}
     *             | IN (value_list)}]
     *         [[STORAGE] ENGINE [=] engine_name]
     *         [COMMENT [=] 'comment_text' ]
     *         [DATA DIRECTORY [=] 'data_dir']
     *         [INDEX DIRECTORY [=] 'index_dir']
     *         [MAX_ROWS [=] max_number_of_rows]
     *         [MIN_ROWS [=] min_number_of_rows]
     *         [TABLESPACE [=] tablespace_name]
     *         [NODEGROUP [=] number]             // NDB only
     *         [(subpartition_definition [, subpartition_definition] ...)]
     *
     * subpartition_definition:
     *     SUBPARTITION logical_name
     *         [[STORAGE] ENGINE [=] engine_name]
     *         [COMMENT [=] 'comment_text' ]
     *         [DATA DIRECTORY [=] 'data_dir']
     *         [INDEX DIRECTORY [=] 'index_dir']
     *         [MAX_ROWS [=] max_number_of_rows]
     *         [MIN_ROWS [=] min_number_of_rows]
     *         [TABLESPACE [=] tablespace_name]
     *         [NODEGROUP [=] number]             // NDB only
     */
    private function parsePartitionDefinition(TokenList $tokenList, ?PartitioningCondition $condition = null): PartitionDefinition
    {
        $tokenList->expectKeyword(Keyword::PARTITION);
        $name = $tokenList->expectName(EntityType::PARTITION);

        $columns = $type = null;
        if ($condition !== null) {
            $columns = $condition->getColumns() ?? $condition->getExpression();
            if ($columns instanceof ListExpression) {
                $columns = $columns->getItems();
            } elseif ($columns instanceof RootNode) {
                $columns = [$columns];
            }
            $columns = $columns !== null ? count($columns) : null;

            $type = $condition->getType()->getValue();
        }

        $lessThan = $values = null;
        if (($type === PartitioningConditionType::RANGE || $type === PartitioningConditionType::LIST || $type === null) && $tokenList->hasKeyword(Keyword::VALUES)) {
            if (($type === PartitioningConditionType::RANGE || $type === null) && $tokenList->hasKeywords(Keyword::LESS, Keyword::THAN)) {
                if ($tokenList->hasKeyword(Keyword::MAXVALUE)) {
                    $lessThan = new MaxValueLiteral();
                    // check values count
                    if ($columns !== null && $columns !== 1) {
                        throw new ParserException('Count of values does not match count of columns.', $tokenList);
                    }
                } else {
                    $tokenList->expectSymbol('(');

                    $lessThan = [];
                    do {
                        if ($tokenList->hasKeyword(Keyword::MAXVALUE)) {
                            $lessThan[] = new MaxValueLiteral();
                        } else {
                            $lessThan[] = $this->expressionParser->parseExpression($tokenList);
                        }
                        if (!$tokenList->hasSymbol(',')) {
                            break;
                        }
                    } while (true);

                    // check values count
                    if ($columns !== null && $columns !== count($lessThan)) {
                        throw new ParserException('Count of values does not match count of columns.', $tokenList);
                    }
                    $tokenList->expectSymbol(')');
                }
            } elseif (($type === PartitioningConditionType::LIST || $type === null) && $tokenList->hasKeyword(Keyword::IN)) {
                $tokenList->expectSymbol('(');
                $values = [];
                do {
                    $values[] = $value = $this->expressionParser->parseExpression($tokenList);

                    if ($value instanceof SimpleName && strtoupper($value->getName()) === Keyword::MAXVALUE) {
                        // check MAXVALUE
                        throw new ParserException('MAXVALUE is not allowed in values list.', $tokenList);
                    } elseif ($value instanceof Parentheses) {
                        // check values count
                        if ($columns === 1) {
                            throw new ParserException('Row expressions in VALUES IN only allowed for multi-field column partitioning.', $tokenList);
                        }
                        $list = $value->getContents();
                        if ($list instanceof ListExpression) {
                            $items = $list->getItems();
                            // check MAXVALUE
                            foreach ($items as $item) {
                                if ($item instanceof SimpleName && strtoupper($item->getName()) === Keyword::MAXVALUE) {
                                    throw new ParserException('MAXVALUE is not allowed in values list.', $tokenList);
                                }
                            }
                            // check values count
                            if ($columns !== null && $columns !== count($items)) {
                                throw new ParserException('Count of values does not match count of columns.', $tokenList);
                            } elseif (count($items) > 16) {
                                throw new ParserException('More than 16 columns are not allowed.', $tokenList);
                            }
                        }
                    } elseif ($columns > 1) {
                        // check values count
                        throw new ParserException('Count of values does not match count of columns.', $tokenList);
                    }
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
        } elseif ($columns !== null && $type === PartitioningConditionType::LIST) {
            throw new ParserException('Missing values definition.', $tokenList);
        } elseif ($columns !== null && $type === PartitioningConditionType::RANGE) {
            throw new ParserException('Missing values definition.', $tokenList);
        }

        $options = $this->parsePartitionOptions($tokenList);

        $subpartitions = null;
        if ($tokenList->hasSymbol('(')) {
            $subpartitions = [];
            do {
                $tokenList->expectKeyword(Keyword::SUBPARTITION);
                $subName = $tokenList->expectName(EntityType::PARTITION);
                $subOptions = $this->parsePartitionOptions($tokenList);
                $subpartitions[$subName] = $subOptions;
            } while ($tokenList->hasSymbol(','));
            $tokenList->expectSymbol(')');
        }

        return new PartitionDefinition($name, $lessThan, $values, $options, $subpartitions);
    }

    /**
     * options:
     *     [[STORAGE] ENGINE [=] engine_name]
     *     [COMMENT [=] 'comment_text' ]
     *     [DATA DIRECTORY [=] 'data_dir']
     *     [INDEX DIRECTORY [=] 'index_dir']
     *     [MAX_ROWS [=] max_number_of_rows]
     *     [MIN_ROWS [=] min_number_of_rows]
     *     [TABLESPACE [=] tablespace_name]
     *     [NODEGROUP [=] number]             // NDB only
     *
     * @return non-empty-array<PartitionOption::*, int|string|StorageEngine>|null
     */
    private function parsePartitionOptions(TokenList $tokenList): ?array
    {
        $options = [];

        $keywords = [
            Keyword::STORAGE, Keyword::ENGINE, Keyword::COMMENT, Keyword::DATA, Keyword::INDEX, Keyword::MAX_ROWS,
            Keyword::MIN_ROWS, Keyword::TABLESPACE, Keyword::NODEGROUP,
        ];
        while (($keyword = $tokenList->getAnyKeyword(...$keywords)) !== null) {
            switch ($keyword) {
                case Keyword::STORAGE:
                    $tokenList->expectKeyword(Keyword::ENGINE);
                case Keyword::ENGINE:
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::ENGINE] = $tokenList->expectStorageEngineName();
                    break;
                case Keyword::COMMENT:
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::COMMENT] = $tokenList->expectString();
                    break;
                case Keyword::DATA:
                    $tokenList->expectKeyword(Keyword::DIRECTORY);
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::DATA_DIRECTORY] = $tokenList->expectString();
                    break;
                case Keyword::INDEX:
                    $tokenList->expectKeyword(Keyword::DIRECTORY);
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::INDEX_DIRECTORY] = $tokenList->expectString();
                    break;
                case Keyword::MAX_ROWS:
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::MAX_ROWS] = (int) $tokenList->expectUnsignedInt();
                    break;
                case Keyword::MIN_ROWS:
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::MIN_ROWS] = (int) $tokenList->expectUnsignedInt();
                    break;
                case Keyword::TABLESPACE:
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::TABLESPACE] = $tokenList->expectNonReservedName(EntityType::TABLESPACE);
                    break;
                case Keyword::NODEGROUP:
                    $tokenList->passSymbol('=');
                    $options[PartitionOption::NODEGROUP] = (int) $tokenList->expectUnsignedInt();
                    break;
            }
        }

        return $options !== [] ? $options : null;
    }

    /**
     * @return non-empty-list<string>|null
     */
    private function parsePartitionNames(TokenList $tokenList): ?array
    {
        if ($tokenList->hasKeyword(Keyword::ALL)) {
            return null;
        }
        $names = [];
        do {
            $names[] = $tokenList->expectName(EntityType::PARTITION);
        } while ($tokenList->hasSymbol(','));

        return $names;
    }

    /**
     * @return list<string>
     */
    private function parseColumnList(TokenList $tokenList): array
    {
        $columns = [];
        $tokenList->expectSymbol('(');
        if ($tokenList->hasSymbol(')')) {
            return $columns;
        }

        do {
            $columns[] = $tokenList->expectName(EntityType::COLUMN);
        } while ($tokenList->hasSymbol(','));
        $tokenList->expectSymbol(')');

        return $columns;
    }

    /**
     * @return non-empty-list<string>
     */
    private function parseNonEmptyColumnList(TokenList $tokenList): array
    {
        $columns = [];
        $tokenList->expectSymbol('(');

        do {
            $columns[] = $tokenList->expectName(EntityType::COLUMN);
        } while ($tokenList->hasSymbol(','));
        $tokenList->expectSymbol(')');

        return $columns;
    }

    /**
     * DROP [TEMPORARY] TABLE [IF EXISTS]
     *     tbl_name [, tbl_name] ...
     *     [RESTRICT | CASCADE]
     */
    public function parseDropTable(TokenList $tokenList): DropTableCommand
    {
        $tokenList->expectKeyword(Keyword::DROP);
        $temporary = $tokenList->hasKeyword(Keyword::TEMPORARY);
        $tokenList->expectAnyKeyword(Keyword::TABLE, Keyword::TABLES);
        $ifExists = $tokenList->hasKeyword(Keyword::IF);
        if ($ifExists) {
            $tokenList->expectKeyword(Keyword::EXISTS);
        }
        $tables = [];
        do {
            $tables[] = $tokenList->expectObjectIdentifier();
        } while ($tokenList->hasSymbol(','));

        // ignored in MySQL 5.7, 8.0
        $cascadeRestrict = $tokenList->getAnyKeyword(Keyword::CASCADE, Keyword::RESTRICT);
        $cascadeRestrict = $cascadeRestrict === Keyword::CASCADE ? true : ($cascadeRestrict === Keyword::RESTRICT ? false : null);

        return new DropTableCommand($tables, $temporary, $ifExists, $cascadeRestrict);
    }

    /**
     * RENAME TABLE tbl_name TO new_tbl_name
     *     [, tbl_name2 TO new_tbl_name2] ...
     */
    public function parseRenameTable(TokenList $tokenList): RenameTableCommand
    {
        $tokenList->expectKeyword(Keyword::RENAME);
        $tokenList->expectAnyKeyword(Keyword::TABLE, Keyword::TABLES);

        $tables = [];
        $newTables = [];
        do {
            $tables[] = $tokenList->expectObjectIdentifier();
            $tokenList->expectKeyword(Keyword::TO);
            $newTables[] = $tokenList->expectObjectIdentifier();
        } while ($tokenList->hasSymbol(','));

        return new RenameTableCommand($tables, $newTables);
    }

    /**
     * TRUNCATE [TABLE] tbl_name
     */
    public function parseTruncateTable(TokenList $tokenList): TruncateTableCommand
    {
        $tokenList->expectKeyword(Keyword::TRUNCATE);
        $tokenList->passKeyword(Keyword::TABLE);
        $table = $tokenList->expectObjectIdentifier();

        return new TruncateTableCommand($table);
    }

}
