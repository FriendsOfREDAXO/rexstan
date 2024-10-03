<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Set;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Assignment;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Expression\DefaultLiteral;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\StatementImpl;

class SetNamesCommand extends StatementImpl implements SetCommand
{

    /** @var Charset|DefaultLiteral */
    private SqlSerializable $charset;

    private ?Collation $collation;

    /** @var list<Assignment> */
    private array $assignments;

    /**
     * @param Charset|DefaultLiteral $charset
     * @param list<Assignment> $assignments
     */
    public function __construct(SqlSerializable $charset, ?Collation $collation, array $assignments = [])
    {
        $this->charset = $charset;
        $this->collation = $collation;
        $this->assignments = $assignments;
    }

    /**
     * @return Charset|DefaultLiteral
     */
    public function getCharset(): SqlSerializable
    {
        return $this->charset;
    }

    public function getCollation(): ?Collation
    {
        return $this->collation;
    }

    /**
     * @return list<Assignment>
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SET NAMES ' . $this->charset->serialize($formatter)
            . ($this->collation !== null ? ' COLLATE ' . $this->collation->serialize($formatter) : '');

        if ($this->assignments !== []) {
            $result .= ', ' . $formatter->formatSerializablesList($this->assignments);
        }

        return $result;
    }

}
