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

class AlterSchemaCommand extends StatementImpl implements SchemaCommand
{

    private ?string $schema;

    private SchemaOptions $options;

    public function __construct(?string $schema, SchemaOptions $options)
    {
        $this->schema = $schema;
        $this->options = $options;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public function getOptions(): SchemaOptions
    {
        return $this->options;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER SCHEMA';
        if ($this->schema !== null) {
            $result .= ' ' . $formatter->formatName($this->schema);
        }

        return $result . ' ' . $this->options->serialize($formatter);
    }

}
