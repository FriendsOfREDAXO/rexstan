<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\View;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\SchemaObjectsCommand;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class DropViewCommand extends StatementImpl implements ViewCommand, SchemaObjectsCommand
{

    /** @var non-empty-list<ObjectIdentifier> */
    private array $views;

    private bool $ifExists;

    private ?DropViewOption $option;

    /**
     * @param non-empty-list<ObjectIdentifier> $views
     */
    public function __construct(array $views, bool $ifExists = false, ?DropViewOption $option = null)
    {
        $this->views = $views;
        $this->ifExists = $ifExists;
        $this->option = $option;
    }

    /**
     * @return non-empty-list<ObjectIdentifier>
     */
    public function getViews(): array
    {
        return $this->views;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function getOption(): ?DropViewOption
    {
        return $this->option;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP VIEW ';
        if ($this->ifExists) {
            $result .= 'IF EXISTS ';
        }
        $result .= $formatter->formatSerializablesList($this->views);
        if ($this->option !== null) {
            $result .= ' ' . $this->option->serialize($formatter);
        }

        return $result;
    }

}
