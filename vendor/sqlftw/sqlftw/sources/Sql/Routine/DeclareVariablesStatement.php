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
use SqlFtw\Sql\Expression\ColumnType;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\Statement;

class DeclareVariablesStatement extends Statement implements SqlSerializable
{

    /** @var non-empty-list<string> */
    private array $variables;

    private ColumnType $type;

    private ?RootNode $default;

    /**
     * @param non-empty-list<string> $variables
     */
    public function __construct(array $variables, ColumnType $type, ?RootNode $default = null)
    {
        $this->variables = $variables;
        $this->type = $type;
        $this->default = $default;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getType(): ColumnType
    {
        return $this->type;
    }

    public function getDefault(): ?RootNode
    {
        return $this->default;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DECLARE ' . $formatter->formatNamesList($this->variables) . ' ' . $this->type->serialize($formatter);
        if ($this->default !== null) {
            $result .= ' DEFAULT ' . $formatter->formatValue($this->default);
        }

        return $result;
    }

}
