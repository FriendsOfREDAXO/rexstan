<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Index;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableAlgorithm;
use SqlFtw\Sql\Ddl\Table\Alter\AlterTableLock;
use SqlFtw\Sql\Ddl\Table\DdlTableCommand;
use SqlFtw\Sql\Ddl\Table\Index\IndexDefinition;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\StatementImpl;

class CreateIndexCommand extends StatementImpl implements IndexCommand, DdlTableCommand
{

    private IndexDefinition $definition;

    private ?AlterTableAlgorithm $algorithm;

    private ?AlterTableLock $lock;

    public function __construct(
        IndexDefinition $definition,
        ?AlterTableAlgorithm $algorithm = null,
        ?AlterTableLock $lock = null
    ) {
        if ($definition->getTable() === null) {
            throw new InvalidDefinitionException('Index must have a table.');
        }
        if ($definition->getName() === null) {
            throw new InvalidDefinitionException('Index must have a name.');
        }

        $this->definition = $definition;
        $this->algorithm = $algorithm;
        $this->lock = $lock;
    }

    public function getIndex(): ObjectIdentifier
    {
        /** @var string $name */
        $name = $this->definition->getName();
        $table = $this->getTable();
        $schema = $table instanceof QualifiedName ? $table->getSchema() : null;

        return $schema !== null ? new QualifiedName($name, $schema) : new SimpleName($name);
    }

    public function getTable(): ObjectIdentifier
    {
        /** @var ObjectIdentifier $table */
        $table = $this->definition->getTable();

        return $table;
    }

    public function getIndexDefinition(): IndexDefinition
    {
        return $this->definition;
    }

    public function getAlgorithm(): ?AlterTableAlgorithm
    {
        return $this->algorithm;
    }

    public function getLock(): ?AlterTableLock
    {
        return $this->lock;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CREATE ';
        $result .= $this->definition->serializeHead($formatter);
        $result .= ' ON ' . $this->getTable()->serialize($formatter);
        $result .= ' ' . $this->definition->serializeTail($formatter);

        if ($this->algorithm !== null) {
            $result .= ' ALGORITHM ' . $this->algorithm->serialize($formatter);
        }
        if ($this->lock !== null) {
            $result .= ' LOCK ' . $this->lock->serialize($formatter);
        }

        return $result;
    }

}
