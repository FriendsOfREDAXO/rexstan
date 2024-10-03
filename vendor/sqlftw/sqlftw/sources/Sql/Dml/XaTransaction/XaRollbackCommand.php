<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\XaTransaction;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\StatementImpl;

class XaRollbackCommand extends StatementImpl implements XaTransactionCommand
{

    private Xid $xid;

    public function __construct(Xid $xid)
    {
        $this->xid = $xid;
    }

    public function getXid(): Xid
    {
        return $this->xid;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'XA ROLLBACK ' . $this->xid->serialize($formatter);
    }

}
