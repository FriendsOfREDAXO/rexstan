<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Show;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\UserName;

class ShowCreateUserCommand extends Statement implements ShowCommand
{

    private UserName $user;

    public function __construct(UserName $user)
    {
        $this->user = $user;
    }

    public function getUser(): UserName
    {
        return $this->user;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SHOW CREATE USER ' . $this->user->serialize($formatter);
    }

}
