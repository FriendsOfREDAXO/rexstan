<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\TableReference;

abstract class Join implements TableReferenceNode
{

    protected TableReferenceNode $left;

    protected TableReferenceNode $right;

    public function __construct(TableReferenceNode $left, TableReferenceNode $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function getLeft(): TableReferenceNode
    {
        return $this->left;
    }

    public function getRight(): TableReferenceNode
    {
        return $this->right;
    }

}
