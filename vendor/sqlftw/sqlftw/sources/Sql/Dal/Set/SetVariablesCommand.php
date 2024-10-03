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
use SqlFtw\Sql\StatementImpl;

class SetVariablesCommand extends StatementImpl implements SetCommand
{

    /** @var non-empty-list<Assignment> */
    private array $assignments;

    /**
     * @param non-empty-list<Assignment> $assignments
     */
    public function __construct(array $assignments)
    {
        $this->assignments = $assignments;
    }

    /**
     * @return non-empty-list<Assignment>
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SET ' . $formatter->formatSerializablesList($this->assignments);
    }

}
