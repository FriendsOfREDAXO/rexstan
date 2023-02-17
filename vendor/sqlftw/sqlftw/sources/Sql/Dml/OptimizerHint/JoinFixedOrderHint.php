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

class JoinFixedOrderHint implements OptimizerHint
{

    private ?string $queryBlock;

    public function __construct(?string $queryBlock = null)
    {
        $this->queryBlock = $queryBlock;
    }

    public function getType(): string
    {
        return OptimizerHintType::JOIN_FIXED_ORDER;
    }

    public function getQueryBlock(): ?string
    {
        return $this->queryBlock;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'JOIN_FIXED_ORDER(' . ($this->queryBlock !== null ? '@' . $formatter->formatName($this->queryBlock) : '') . ')';
    }

}
