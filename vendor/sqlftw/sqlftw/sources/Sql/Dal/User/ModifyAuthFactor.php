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
use SqlFtw\Sql\InvalidDefinitionException;

class ModifyAuthFactor implements AlterUserAction
{

    private int $factor1;

    private AuthOption $option1;

    private ?int $factor2;

    private ?AuthOption $option2;

    public function __construct(
        int $factor1,
        AuthOption $option1,
        ?int $factor2 = null,
        ?AuthOption $option2 = null
    ) {
        if (!(($factor2 === null) xor ($option2 === null))) {
            throw new InvalidDefinitionException('Both $factor2 and $option2 must be set or both must be null.');
        }

        $this->factor1 = $factor1;
        $this->option1 = $option1;
        $this->factor2 = $factor2;
        $this->option2 = $option2;
    }

    public function getFactor1(): int
    {
        return $this->factor1;
    }

    public function getOption1(): ?AuthOption
    {
        return $this->option1;
    }

    public function getFactor2(): ?int
    {
        return $this->factor2;
    }

    public function getOption2(): ?AuthOption
    {
        return $this->option2;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'MODIFY ' . $this->factor1 . ' ' . $this->option1->serialize($formatter);

        if ($this->factor2 !== null && $this->option2 !== null) {
            $result .= ' MODIFY ' . $this->factor2 . ' ' . $this->option2->serialize($formatter);
        }

        return $result;
    }

}
