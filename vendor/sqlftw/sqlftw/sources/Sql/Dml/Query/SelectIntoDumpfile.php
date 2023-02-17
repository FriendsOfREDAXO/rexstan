<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Formatter\Formatter;

class SelectIntoDumpfile extends SelectInto
{

    private string $fileName;

    /**
     * @param SelectInto::POSITION_* $position
     */
    public function __construct(string $fileName, int $position = self::POSITION_AFTER_LOCKING)
    {
        $this->fileName = $fileName;
        $this->position = $position;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'INTO DUMPFILE ' . $formatter->formatString($this->fileName);
    }

}
