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

class SelectIntoDumpfile implements SelectInto
{

    private string $fileName;

    /** @var self::POSITION_* */
    protected int $position;

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

    /**
     * @return self::POSITION_*
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'INTO DUMPFILE ' . $formatter->formatString($this->fileName);
    }

}
