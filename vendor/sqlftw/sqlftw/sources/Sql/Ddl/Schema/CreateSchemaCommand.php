<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Schema;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class CreateSchemaCommand extends StatementImpl implements SchemaCommand
{

    private string $schema;

    private ?SchemaOptions $options;

    private bool $ifNotExists;

    public function __construct(string $schema, ?SchemaOptions $options, bool $ifNotExists = false)
    {
        $this->schema = $schema;
        $this->options = $options;
        $this->ifNotExists = $ifNotExists;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getOptions(): SchemaOptions
    {
        return $this->options ?? new SchemaOptions();
    }

    public function ifNotExists(): bool
    {
        return $this->ifNotExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CREATE SCHEMA ';
        if ($this->ifNotExists) {
            $result .= 'IF NOT EXISTS ';
        }

        $result .= $formatter->formatName($this->schema);

        if ($this->options !== null) {
            $result .= ' ' . $this->options->serialize($formatter);
        }

        return $result;
    }

}
