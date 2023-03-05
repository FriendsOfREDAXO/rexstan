<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Import;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dml\DmlCommand;
use SqlFtw\Sql\Statement;

class ImportCommand extends Statement implements DmlCommand
{

    /** @var non-empty-list<string> */
    private array $files;

    /**
     * @param non-empty-list<string> $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'IMPORT TABLE FROM ' . $formatter->formatStringList($this->files);
    }

}
