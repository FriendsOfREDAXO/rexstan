<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Insert;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\Assignment;
use SqlFtw\Sql\SqlSerializable;

class OnDuplicateKeyActions implements SqlSerializable
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
        $result = 'ON DUPLICATE KEY UPDATE ';

        $result .= $formatter->formatSerializablesList($this->assignments);

        return $result;
    }

}
