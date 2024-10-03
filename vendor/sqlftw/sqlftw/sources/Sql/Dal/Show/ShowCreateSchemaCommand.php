<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class ShowCreateSchemaCommand extends StatementImpl implements ShowCommand
{

    private string $schema;

    public function __construct(string $schema)
    {
        $this->schema = $schema;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SHOW CREATE DATABASE ' . $formatter->formatName($this->schema);
    }

}
