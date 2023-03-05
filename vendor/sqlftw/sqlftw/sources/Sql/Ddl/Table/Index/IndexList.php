<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Index;

use function is_string;

class IndexList
{

    /** @var array<string|int, IndexDefinition> ($name => $index) */
    private array $indexes = [];

    /** @var array<string|int, IndexDefinition> ($name => $index) */
    private array $renamedIndexes = [];

    /** @var array<string|int, IndexDefinition> ($name => $index) */
    private array $droppedIndexes = [];

    /**
     * @param array<string|int, IndexDefinition> $indexes
     */
    public function __construct(array $indexes)
    {
        foreach ($indexes as $index) {
            $this->addIndex($index);
        }
    }

    private function addIndex(IndexDefinition $index): void
    {
        if ($index->getName() !== null) {
            $this->indexes[$index->getName()] = $index;
        } else {
            $this->indexes[] = $index;
        }
    }

    public function updateRenamedIndex(IndexDefinition $renamedIndex, ?string $newName): void
    {
        foreach ($this->indexes as $oldName => $index) {
            if ($index === $renamedIndex) {
                unset($this->indexes[$oldName]);
                if ($newName !== null) {
                    $this->indexes[$newName] = $index;
                } else {
                    $this->indexes[] = $index;
                }
            }
        }
    }

    /**
     * @return array<string|int, IndexDefinition>
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function containsIndex(IndexDefinition $searchedIndex): bool
    {
        foreach ($this->indexes as $index) {
            if ($index === $searchedIndex) {
                return true;
            }
        }

        return false;
    }

    public function getPrimaryKey(): ?IndexDefinition
    {
        foreach ($this->indexes as $index) {
            if ($index->isPrimary()) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return array<string|int, IndexDefinition>
     */
    public function getUniqueKeys(): array
    {
        $keys = [];
        foreach ($this->indexes as $name => $index) {
            if ($index->isUnique()) {
                if (is_string($name)) {
                    $keys[$index->getName()] = $index;
                } else {
                    $keys[] = $index;
                }
            }
        }

        return $keys;
    }

    /**
     * @return array<string|int, IndexDefinition>
     */
    public function getRenamedIndexes(): array
    {
        return $this->renamedIndexes;
    }

    /**
     * @return array<string|int, IndexDefinition>
     */
    public function getDroppedIndexes(): array
    {
        return $this->droppedIndexes;
    }

}
