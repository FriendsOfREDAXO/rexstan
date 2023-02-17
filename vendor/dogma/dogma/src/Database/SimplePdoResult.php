<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;

use Dogma\StrictBehaviorMixin;
use Exception;
use Iterator;
use PDO;
use PDOStatement;
use ReturnTypeWillChange;
use function is_array;
use function is_int;

/**
 * @deprecated will be removed
 * @implements Iterator<int, mixed[]>
 */
class SimplePdoResult implements Iterator
{
    use StrictBehaviorMixin;

    /** @var PDOStatement<int, mixed[]> */
    private $statement;

    /** @var int */
    private $key;

    /** @var mixed[]|bool */
    private $current;

    /**
     * @param PDOStatement<int, mixed[]> $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    public function columnCount(): int
    {
        return $this->statement->columnCount();
    }

    /**
     * @return mixed[]|bool
     */
    public function fetch(int $mode = PDO::FETCH_ASSOC)
    {
        return $this->statement->fetch($mode);
    }

    /**
     * @return mixed[][]
     */
    public function fetchAll(int $mode = PDO::FETCH_ASSOC): array
    {
        /** @var mixed[][] $result */
        $result = $this->statement->fetchAll($mode);
        $this->close();
        unset($this->statement);

        return $result;
    }

    /**
     * @param int|string $column
     * @return mixed|bool
     */
    public function fetchColumn($column = 0)
    {
        if (is_int($column)) {
            return $this->statement->fetch(PDO::FETCH_NUM)[$column] ?? false;
        } else {
            return $this->statement->fetch(PDO::FETCH_ASSOC)[$column] ?? false;
        }
    }

    /**
     * @param int|string $column
     * @return mixed[]
     */
    public function fetchColumnAll($column): array
    {
        /** @var mixed[][] $result */
        $result = $this->statement->fetchAll(is_int($column) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC);
        $rows = [];
        foreach ($result as $row) {
            $rows[] = $row[$column];
        }

        return $rows;
    }

    public function close(): void
    {
        $this->statement->closeCursor();
    }

    public function rewind(): void
    {
        $this->key = 0;
        $this->current = $this->fetch();
    }

    #[ReturnTypeWillChange]
    public function next(): bool
    {
        $this->key++;
        $this->current = $this->fetch();

        return $this->current !== false;
    }

    public function valid(): bool
    {
        if ($this->current === false) {
            $this->statement->closeCursor();
        }
        return $this->current !== false;
    }

    /**
     * @return mixed[]
     */
    public function current(): array
    {
        if (is_array($this->current)) {
            return $this->current;
        } else {
            throw new Exception();
        }
    }

    public function key(): int
    {
        return $this->key;
    }

}
