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

class MaxExecutionTimeHint implements OptimizerHint
{

    private int $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public function getType(): string
    {
        return OptimizerHintType::MAX_EXECUTION_TIME;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'MAX_EXECUTION_TIME(' . $this->limit . ')';
    }

}
