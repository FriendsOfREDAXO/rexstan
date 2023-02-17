<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table;

use Dogma\CombineIterator;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Statement;
use function array_values;
use function count;
use function rtrim;

class RenameTableCommand extends Statement implements DdlTablesCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    protected array $names;

    /** @var non-empty-list<ObjectIdentifier> */
    private array $newNames;

    /**
     * @param non-empty-list<ObjectIdentifier> $names
     * @param non-empty-list<ObjectIdentifier> $newNames
     */
    public function __construct(array $names, array $newNames)
    {
        if (count($names) !== count($newNames)) {
            throw new InvalidDefinitionException('Count of old table names and new table names do not match.');
        }

        $this->names = array_values($names);
        $this->newNames = array_values($newNames);
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getTables(): array
    {
        return $this->names;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getNewNames(): array
    {
        return $this->newNames;
    }

    public function getNewNameForTable(ObjectIdentifier $table): ?ObjectIdentifier
    {
        /**
         * @var ObjectIdentifier $old
         * @var ObjectIdentifier $new
         */
        foreach ($this->getIterator() as $old => $new) {
            if ($old->getName() !== $table->getName()) {
                continue;
            }
            $oldSchema = $old instanceof QualifiedName ? $old->getSchema() : null;
            $targetSchema = $new instanceof QualifiedName ? $new->getSchema() : null;
            if ($oldSchema === null || $oldSchema === $targetSchema) {
                return $targetSchema === null ? new SimpleName($new->getName()) : $new;
            }
        }

        return null;
    }

    public function getIterator(): CombineIterator
    {
        return new CombineIterator($this->names, $this->newNames);
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'RENAME TABLE';
        foreach ($this->names as $i => $table) {
            $result .= ' ' . $table->serialize($formatter) . ' TO ' . $this->newNames[$i]->serialize($formatter) . ',';
        }

        return rtrim($result, ',');
    }

}
