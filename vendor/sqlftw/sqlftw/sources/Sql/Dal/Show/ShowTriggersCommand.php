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
use SqlFtw\Sql\StatementImpl;

class ShowTriggersCommand extends StatementImpl implements ShowCommand
{

    private ?string $schema;

    private ?string $like;

    private ?RootNode $where;

    public function __construct(?string $schema = null, ?string $like = null, ?RootNode $where = null)
    {
        $this->schema = $schema;
        $this->like = $like;
        $this->where = $where;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
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
        $result = 'SHOW TRIGGERS';
        if ($this->schema !== null) {
            $result .= ' FROM ' . $formatter->formatName($this->schema);
        }
        if ($this->like !== null) {
            $result .= ' LIKE ' . $formatter->formatString($this->like);
        } elseif ($this->where !== null) {
            $result .= ' WHERE ' . $this->where->serialize($formatter);
        }

        return $result;
    }

}
