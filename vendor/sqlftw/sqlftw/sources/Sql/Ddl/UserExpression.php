<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\BuiltInFunction;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\UserName;

class UserExpression implements SqlSerializable
{

    /** @var UserName|BuiltInFunction */
    private SqlSerializable $user;

    /**
     * @param UserName|BuiltInFunction $user
     */
    public function __construct(SqlSerializable $user)
    {
        if ($user instanceof BuiltInFunction && !$user->equalsValue(BuiltInFunction::CURRENT_USER)) {
            throw new InvalidDefinitionException('Only CURRENT_USER function is accepted in place of user name.');
        }

        $this->user = $user;
    }

    /**
     * @return UserName|BuiltInFunction
     */
    public function getUser(): SqlSerializable
    {
        return $this->user;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->user->serialize($formatter);
    }

}
