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
use SqlFtw\Sql\Statement;

class SetRoleCommand extends Statement implements UserCommand
{

    private RolesSpecification $role;

    public function __construct(RolesSpecification $role)
    {
        $this->role = $role;
    }

    public function getRole(): RolesSpecification
    {
        return $this->role;
    }

    public function serialize(Formatter $formatter): string
    {
        return 'SET ROLE ' . $this->role->serialize($formatter);
    }

}
