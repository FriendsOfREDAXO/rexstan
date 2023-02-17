<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ExpressionNode;
use SqlFtw\Sql\Statement;

class ResetMasterCommand extends Statement implements ReplicationCommand
{

    private ?ExpressionNode $binlogPosition;

    public function __construct(?ExpressionNode $binlogPosition)
    {
        $this->binlogPosition = $binlogPosition;
    }

    public function getBinlogPosition(): ?ExpressionNode
    {
        return $this->binlogPosition;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'RESET MASTER';
        if ($this->binlogPosition !== null) {
            $result .= ' TO ' . $this->binlogPosition->serialize($formatter);
        }

        return $result;
    }

}
