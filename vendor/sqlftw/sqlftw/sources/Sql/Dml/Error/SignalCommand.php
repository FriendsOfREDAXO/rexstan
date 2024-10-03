<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Error;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\StatementImpl;

class SignalCommand extends StatementImpl implements ErrorHandlingCommand
{

    /** @var SqlState|string|null */
    private $condition;

    /** @var array<ConditionInformationItem::*, RootNode> */
    private array $items;

    /**
     * @param SqlState|string|null $condition
     * @param array<ConditionInformationItem::*, RootNode> $items
     */
    public function __construct($condition, array $items)
    {
        foreach ($items as $key => $value) {
            ConditionInformationItem::checkValue($key);
        }
        $this->condition = $condition;
        $this->items = $items;
    }

    /**
     * @return SqlState|string|null
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return array<ConditionInformationItem::*, RootNode>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SIGNAL';
        if ($this->condition instanceof SqlState) {
            $result .= ' SQLSTATE ' . $this->condition->serialize($formatter);
        } elseif ($this->condition !== null) {
            $result .= ' ' . $this->condition;
        }
        if ($this->items !== []) {
            $result .= ' SET ' . $formatter->formatSerializablesMap($this->items);
        }

        return $result;
    }

}
