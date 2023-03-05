<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Statement;

class FetchStatement extends Statement
{

    private string $cursor;

    /** @var non-empty-list<string> */
    private array $variables;

    /**
     * @param non-empty-list<string> $variables
     */
    public function __construct(string $cursor, array $variables)
    {
        $this->cursor = $cursor;
        $this->variables = $variables;
    }

    public function getCursor(): string
    {
        return $this->cursor;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'FETCH NEXT FROM ' . $formatter->formatName($this->cursor)
            . ' INTO ' . $formatter->formatNamesList($this->variables);
    }

}
