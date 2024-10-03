<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\Expression\Scope;
use SqlFtw\Sql\StatementImpl;

class ShowStatusCommand extends StatementImpl implements ShowCommand
{

    private ?Scope $scope;

    private ?string $like;

    private ?RootNode $where;

    public function __construct(?Scope $scope = null, ?string $like = null, ?RootNode $where = null)
    {
        $this->scope = $scope;
        $this->like = $like;
        $this->where = $where;
    }

    public function getScope(): ?Scope
    {
        return $this->scope;
    }

    public function getLike(): ?string
    {
        return $this->like;
    }

    public function getWhere(): ?RootNode
    {
        return $this->where;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SHOW';
        if ($this->scope !== null) {
            $result .= ' ' . $this->scope->serialize($formatter);
        }
        $result .= ' STATUS';
        if ($this->like !== null) {
            $result .= ' LIKE ' . $formatter->formatString($this->like);
        } elseif ($this->where !== null) {
            $result .= ' WHERE ' . $this->where->serialize($formatter);
        }

        return $result;
    }

}
