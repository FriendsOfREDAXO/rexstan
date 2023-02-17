<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\OptimizerHint;

use SqlFtw\Formatter\Formatter;

class QueryBlockNameHint implements OptimizerHint
{

    private string $queryBlock;

    public function __construct(string $queryBlock)
    {
        $this->queryBlock = $queryBlock;
    }

    public function getType(): string
    {
        return OptimizerHintType::RESOURCE_GROUP;
    }

    public function getQueryBlock(): string
    {
        return $this->queryBlock;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'QB_NAME(' . $this->queryBlock . ')';
    }

}
