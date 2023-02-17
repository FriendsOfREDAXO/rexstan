<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\SimpleName;
use SqlFtw\Sql\Expression\UserVariable;

class SelectIntoVariables extends SelectInto
{

    /** @var non-empty-list<UserVariable|SimpleName> */
    private array $variables;

    /**
     * @param non-empty-list<UserVariable|SimpleName> $variables
     * @param SelectInto::POSITION_* $position
     */
    public function __construct(array $variables, int $position = self::POSITION_AFTER_LOCKING)
    {
        $this->variables = $variables;
        $this->position = $position;
    }

    /**
     * @return non-empty-list<UserVariable|SimpleName>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'INTO ' . $formatter->formatSerializablesList($this->variables);
    }

}
