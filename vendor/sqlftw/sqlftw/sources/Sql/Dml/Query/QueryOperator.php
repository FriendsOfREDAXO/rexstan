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
use SqlFtw\Sql\SqlSerializable;

class QueryOperator implements SqlSerializable
{

    private QueryOperatorType $type;

    private QueryOperatorOption $option;

    public function __construct(QueryOperatorType $type, QueryOperatorOption $option)
    {
        $this->type = $type;
        $this->option = $option;
    }

    public function getType(): QueryOperatorType
    {
        return $this->type;
    }

    public function getOption(): QueryOperatorOption
    {
        return $this->option;
    }

    public function serialize(Formatter $formatter): string
    {
        $option = $this->option->serialize($formatter);

        return $this->type->serialize($formatter) . ($option !== '' ? ' ' . $option : '');
    }

}
