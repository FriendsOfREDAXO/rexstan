<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Option;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\SqlEnum;

/**
 * Case-insensitive, but needs to be a string
 */
class TableCompression extends SqlEnum
{

    public const ZLIB = 'ZLIB';
    public const LZ4 = 'LZ4';
    public const NONE = 'NONE';

    public function serialize(Formatter $formatter): string
    {
        return "'" . $this->getValue() . "'";
    }

}
