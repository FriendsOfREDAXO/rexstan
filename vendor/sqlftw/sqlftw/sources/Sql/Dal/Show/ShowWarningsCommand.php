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
use SqlFtw\Sql\Statement;

class ShowWarningsCommand extends Statement implements ShowCommand
{

    private ?int $limit;

    private ?int $offset;

    private bool $count = false;

    public function __construct(?int $limit = null, ?int $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public static function createCount(): self
    {
        $self = new self();
        $self->count = true;

        return $self;
    }

    public function isCount(): bool
    {
        return $this->count;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function serialize(Formatter $formatter): string
    {
        if ($this->count) {
            return 'SHOW COUNT(*) WARNINGS';
        }

        $result = 'SHOW WARNINGS';
        if ($this->limit !== null) {
            $result .= ' LIMIT ' . $this->limit;
            if ($this->offset !== null) {
                $result .= ' OFFSET ' . $this->offset;
            }
        }

        return $result;
    }

}
