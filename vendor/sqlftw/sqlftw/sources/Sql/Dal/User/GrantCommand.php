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
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\UserName;

class GrantCommand extends Statement implements UserCommand
{

    /** @var non-empty-list<UserPrivilege> */
    private array $privileges;

    private UserPrivilegeResource $resource;

    /** @var non-empty-list<IdentifiedUser> */
    private array $users;

    /** @var UserName|FunctionCall|null */
    private ?SqlSerializable $asUser;

    private ?RolesSpecification $withRole;

    /** @var list<UserTlsOption>|null */
    private ?array $tlsOptions;

    /** @var non-empty-list<UserResourceOption>|null */
    private ?array $resourceOptions;

    private bool $withGrantOption;

    /**
     * @param non-empty-list<UserPrivilege> $privileges
     * @param non-empty-list<IdentifiedUser> $users
     * @param UserName|FunctionCall|null $asUser
     * @param list<UserTlsOption>|null $tlsOptions
     * @param non-empty-list<UserResourceOption>|null $resourceOptions
     */
    public function __construct(
        array $privileges,
        UserPrivilegeResource $resource,
        array $users,
        ?SqlSerializable $asUser = null,
        ?RolesSpecification $withRole = null,
        ?array $tlsOptions = null,
        ?array $resourceOptions = null,
        bool $withGrantOption = false
    ) {
        $this->privileges = $privileges;
        $this->resource = $resource;
        $this->users = $users;
        $this->asUser = $asUser;
        $this->withRole = $withRole;
        $this->tlsOptions = $tlsOptions;
        $this->resourceOptions = $resourceOptions;
        $this->withGrantOption = $withGrantOption;
    }

    /**
     * @return non-empty-list<UserPrivilege>
     */
    public function getPrivileges(): array
    {
        return $this->privileges;
    }

    public function getResource(): UserPrivilegeResource
    {
        return $this->resource;
    }

    /**
     * @return non-empty-list<IdentifiedUser>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return UserName|FunctionCall|null
     */
    public function getAsUser(): ?SqlSerializable
    {
        return $this->asUser;
    }

    public function getWithRole(): ?RolesSpecification
    {
        return $this->withRole;
    }

    /**
     * @return list<UserTlsOption>|null
     */
    public function getTlsOptions(): ?array
    {
        return $this->tlsOptions;
    }

    /**
     * @return non-empty-list<UserResourceOption>|null
     */
    public function getResourceOptions(): ?array
    {
        return $this->resourceOptions;
    }

    public function withGrantOption(): bool
    {
        return $this->withGrantOption;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'GRANT ' . $formatter->formatSerializablesList($this->privileges)
            . ' ON ' . $this->resource->serialize($formatter)
            . ' TO ' . $formatter->formatSerializablesList($this->users);

        if ($this->tlsOptions !== null) {
            $result .= ' REQUIRE';
            if ($this->tlsOptions === []) {
                $result .= ' NONE';
            } else {
                $result .= $formatter->formatSerializablesList($this->tlsOptions);
            }
        }
        if ($this->withGrantOption) {
            $result .= ' WITH GRANT OPTION';
        }
        if ($this->resourceOptions !== null) {
            $result .= ' WITH ' . $formatter->formatSerializablesList($this->resourceOptions);
        }
        if ($this->asUser !== null) {
            $result .= ' AS ' . $this->asUser->serialize($formatter);
            if ($this->withRole !== null) {
                $result .= ' WITH ROLE ' . $this->withRole->serialize($formatter);
            }
        }

        return $result;
    }

}
