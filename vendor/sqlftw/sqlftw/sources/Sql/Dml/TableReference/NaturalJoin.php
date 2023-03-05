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

class NaturalJoin extends Join
{

    private ?JoinSide $joinSide;

    public function __construct(TableReferenceNode $left, TableReferenceNode $right, ?JoinSide $joinSide)
    {
        parent::__construct($left, $right);

        $this->joinSide = $joinSide;
    }

    public function getJoinSide(): ?JoinSide
    {
        return $this->joinSide;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->left->serialize($formatter) . ' NATURAL ';
        if ($this->joinSide !== null) {
            $result .= $this->joinSide->serialize($formatter) . ' ';
        }
        $result .= 'JOIN ' . $this->right->serialize($formatter);

        return $result;
    }

}
