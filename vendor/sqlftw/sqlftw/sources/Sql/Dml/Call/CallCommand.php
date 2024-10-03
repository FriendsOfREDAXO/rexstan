<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Call;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\StatementImpl;

class CallCommand extends StatementImpl implements DmlCommand
{

    private ObjectIdentifier $procedure;

    /** @var list<RootNode>|null */
    private ?array $params;

    /**
     * @param list<RootNode>|null $params
     */
    public function __construct(ObjectIdentifier $procedure, ?array $params = null)
    {
        $this->procedure = $procedure;
        $this->params = $params;
    }

    public function getProcedure(): ObjectIdentifier
    {
        return $this->procedure;
    }

    /**
     * @return list<RootNode>|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CALL ' . $this->procedure->serialize($formatter);
        if ($this->params !== null) {
            $result .= '(';
            if ($this->params !== []) {
                $result .= $formatter->formatSerializablesList($this->params);
            }
            $result .= ')';
        }

        return $result;
    }

}
