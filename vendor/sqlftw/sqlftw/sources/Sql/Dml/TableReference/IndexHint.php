<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\TableReference;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\PrimaryLiteral;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;

class IndexHint implements SqlSerializable
{

    private IndexHintAction $action;

    private ?IndexHintTarget $target;

    /** @var list<string|PrimaryLiteral> */
    private array $indexes;

    /**
     * @param list<string|PrimaryLiteral> $indexes
     */
    public function __construct(IndexHintAction $action, ?IndexHintTarget $target, array $indexes)
    {
        if (!$action->equalsValue(IndexHintAction::USE) && $indexes === []) {
            throw new InvalidDefinitionException('Indexes cannot be empty for action ' . $action->getValue() . '.');
        }
        $this->action = $action;
        $this->target = $target;
        $this->indexes = $indexes;
    }

    public function getAction(): IndexHintAction
    {
        return $this->action;
    }

    public function getTarget(): ?IndexHintTarget
    {
        return $this->target;
    }

    /**
     * @return list<string|PrimaryLiteral>
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->action->serialize($formatter) . ' INDEX';
        if ($this->target !== null) {
            $result .= ' FOR ' . $this->target->serialize($formatter);
        }
        $result .= ' (' . ($this->indexes !== [] ? $formatter->formatNamesList($this->indexes) : '') . ')';

        return $result;
    }

}
