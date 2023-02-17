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
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Statement;

class SetCharacterSetCommand extends Statement implements SetCommand
{

    private ?Charset $charset;

    /** @var list<SetAssignment> */
    private array $assignments;

    /**
     * @param list<SetAssignment> $assignments
     */
    public function __construct(?Charset $charset, array $assignments = [])
    {
        $this->charset = $charset;
        $this->assignments = $assignments;
    }

    public function getCharset(): ?Charset
    {
        return $this->charset;
    }

    /**
     * @return list<SetAssignment>
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'SET CHARACTER SET ' . ($this->charset !== null ? $this->charset->serialize($formatter) : 'DEFAULT');

        if ($this->assignments !== []) {
            $result .= ', ' . $formatter->formatSerializablesList($this->assignments);
        }

        return $result;
    }

}
