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
use SqlFtw\Sql\SqlSerializable;
use SqlFtw\Sql\UserName;

class RolesSpecification implements SqlSerializable
{

    private RolesSpecificationType $type;

    /** @var non-empty-list<UserName>|null */
    private ?array $roles;

    /**
     * @param non-empty-list<UserName>|null $roles
     */
    public function __construct(RolesSpecificationType $type, ?array $roles = null)
    {
        $this->type = $type;
        $this->roles = $roles;
    }

    public function getType(): RolesSpecificationType
    {
        return $this->type;
    }

    /**
     * @return non-empty-list<UserName>|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->type->serialize($formatter);
        if ($this->type->equalsAnyValue(RolesSpecificationType::ALL_EXCEPT)) {
            $result .= ' ';
        }
        if ($this->roles !== null) {
            $result .= $formatter->formatSerializablesList($this->roles);
        }

        return $result;
    }

}
