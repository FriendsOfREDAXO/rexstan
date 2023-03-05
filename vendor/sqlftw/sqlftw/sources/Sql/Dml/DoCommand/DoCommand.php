<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\DoCommand;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Statement;

class DoCommand extends Statement implements DmlCommand
{

    /** @var non-empty-list<ExpressionNode> */
    private array $expressions;

    /**
     * @param non-empty-list<ExpressionNode> $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @return non-empty-list<ExpressionNode>
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'DO ' . $formatter->formatSerializablesList($this->expressions);
    }

}
