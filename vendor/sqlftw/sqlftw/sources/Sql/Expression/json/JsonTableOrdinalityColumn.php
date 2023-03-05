<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;

class JsonTableOrdinalityColumn implements JsonTableColumn
{

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->name . ' FOR ORDINALITY';
    }

}
