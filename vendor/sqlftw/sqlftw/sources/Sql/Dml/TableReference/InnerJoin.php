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
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\InvalidDefinitionException;

class InnerJoin extends Join
{

    private bool $crossJoin;

    private ?RootNode $condition;

    /** @var non-empty-list<string>|null */
    private ?array $using;

    /**
     * @param non-empty-list<string>|null $using
     */
    public function __construct(
        TableReferenceNode $left,
        TableReferenceNode $right,
        bool $crossJoin,
        ?RootNode $condition,
        ?array $using
    ) {
        parent::__construct($left, $right);

        if ($condition !== null && $using !== null) {
            throw new InvalidDefinitionException('Either join condition or USING can be set, not both.');
        }

        $this->crossJoin = $crossJoin;
        $this->condition = $condition;
        $this->using = $using;
    }

    public function isCrossJoin(): bool
    {
        return $this->crossJoin;
    }

    public function getCondition(): ?RootNode
    {
        return $this->condition;
    }

    /**
     * @return non-empty-list<string>|null
     */
    public function getUsing(): ?array
    {
        return $this->using;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->left->serialize($formatter);
        if ($this->crossJoin) {
            $result .= ' CROSS';
        }
        $result .= ' JOIN ' . $this->right->serialize($formatter);

        if ($this->condition !== null) {
            $result .= ' ON ' . $this->condition->serialize($formatter);
        } elseif ($this->using !== null) {
            $result .= ' USING (' . $formatter->formatNamesList($this->using) . ')';
        }

        return $result;
    }

}
