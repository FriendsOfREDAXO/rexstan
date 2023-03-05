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

class StraightJoin extends Join
{

    private ?RootNode $condition;

    /** @var non-empty-list<string>|null */
    private ?array $using;

    /**
     * @param non-empty-list<string>|null $using
     */
    public function __construct(
        TableReferenceNode $left,
        TableReferenceNode $right,
        ?RootNode $condition,
        ?array $using
    ) {
        parent::__construct($left, $right);

        $this->condition = $condition;
        $this->using = $using;
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
        $result = $this->left->serialize($formatter) . ' STRAIGHT_JOIN ' . $this->right->serialize($formatter);

        if ($this->condition !== null) {
            $result .= ' ON ' . $this->condition->serialize($formatter);
        } elseif ($this->using !== null) {
            $result .= ' USING (' . $formatter->formatNamesList($this->using) . ')';
        }

        return $result;
    }

}
