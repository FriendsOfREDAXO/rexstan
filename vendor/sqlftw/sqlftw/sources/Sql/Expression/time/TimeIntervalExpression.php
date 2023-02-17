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

/**
 * e.g. INTERVAL (n + 1) DAY
 */
class TimeIntervalExpression implements TimeInterval
{

    private RootNode $expression;

    private TimeIntervalUnit $unit;

    public function __construct(RootNode $expression, TimeIntervalUnit $unit)
    {
        $this->expression = $expression;
        $this->unit = $unit;
    }

    public function getExpression(): RootNode
    {
        return $this->expression;
    }

    public function getUnit(): TimeIntervalUnit
    {
        return $this->unit;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'INTERVAL ' . $this->expression->serialize($formatter) . ' ' . $this->unit->serialize($formatter);
    }

}
