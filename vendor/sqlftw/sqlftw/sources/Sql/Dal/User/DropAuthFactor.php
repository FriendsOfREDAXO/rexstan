<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\User;

use SqlFtw\Formatter\Formatter;

class DropAuthFactor implements AlterUserAction
{

    private int $factor1;

    private ?int $factor2;

    public function __construct(int $factor1, ?int $factor2 = null)
    {
        $this->factor1 = $factor1;
        $this->factor2 = $factor2;
    }

    public function getFactor1(): int
    {
        return $this->factor1;
    }

    public function getFactor2(): ?int
    {
        return $this->factor2;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP ' . $this->factor1;

        if ($this->factor2 !== null) {
            $result .= ' DROP ' . $this->factor2;
        }

        return $result;
    }

}
