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
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\UserName;

class IdentifiedUser implements SqlSerializable
{

    /** @var UserName|FunctionCall */
    private SqlSerializable $user;

    private ?AuthOption $option1;

    private ?AuthOption $option2;

    private ?AuthOption $option3;

    /**
     * @param UserName|FunctionCall $user
     */
    public function __construct(
        SqlSerializable $user,
        ?AuthOption $option1 = null,
        ?AuthOption $option2 = null,
        ?AuthOption $option3 = null
    ) {
        $this->user = $user;
        $this->option1 = $option1;
        $this->option2 = $option2;
        $this->option3 = $option3;
    }

    /**
     * @return UserName|FunctionCall
     */
    public function getUser(): SqlSerializable
    {
        return $this->user;
    }

    public function getOption1(): ?AuthOption
    {
        return $this->option1;
    }

    public function getOption2(): ?AuthOption
    {
        return $this->option2;
    }

    public function getOption3(): ?AuthOption
    {
        return $this->option3;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->user->serialize($formatter);

        if ($this->option1 !== null) {
            $result .= ' ' . $this->option1->serialize($formatter);
            if ($this->option2 !== null) {
                $result .= ' AND ' . $this->option2->serialize($formatter);
                if ($this->option3 !== null) {
                    $result .= ' AND ' . $this->option3->serialize($formatter);
                }
            }
        }

        return $result;
    }

}
