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
use SqlFtw\Sql\Statement;

class AlterUserCommand extends Statement implements UserCommand
{

    /** @var non-empty-list<AlteredUser> */
    private $users;

    /** @var list<UserTlsOption>|null */
    private ?array $tlsOptions;

    /** @var non-empty-list<UserResourceOption>|null */
    private ?array $resourceOptions;

    /** @var non-empty-list<UserPasswordLockOption>|null */
    private ?array $passwordLockOptions;

    private ?string $comment;

    private ?string $attribute;

    private bool $ifExists;

    /**
     * @param non-empty-list<AlteredUser> $users
     * @param list<UserTlsOption>|null $tlsOptions
     * @param non-empty-list<UserResourceOption>|null $resourceOptions
     * @param non-empty-list<UserPasswordLockOption>|null $passwordLockOptions
     */
    public function __construct(
        array $users,
        ?array $tlsOptions,
        ?array $resourceOptions = null,
        ?array $passwordLockOptions = null,
        ?string $comment = null,
        ?string $attribute = null,
        bool $ifExists = false
    )
    {
        if ($comment !== null && $attribute !== null) {
            throw new InvalidDefinitionException('Comment and attribute cannot be both set.');
        }

        $this->users = $users;
        $this->tlsOptions = $tlsOptions;
        $this->resourceOptions = $resourceOptions;
        $this->passwordLockOptions = $passwordLockOptions;
        $this->comment = $comment;
        $this->attribute = $attribute;
        $this->ifExists = $ifExists;
    }

    /**
     * @return non-empty-list<AlteredUser>
     */
    public function getUsers(): array
    {
        return $this->users;
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

    /**
     * @return non-empty-list<UserPasswordLockOption>|null
     */
    public function getPasswordLockOptions(): ?array
    {
        return $this->passwordLockOptions;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'ALTER USER ';
        if ($this->ifExists) {
            $result .= 'IF EXISTS ';
        }
        $result .= $formatter->formatSerializablesList($this->users);

        if ($this->tlsOptions !== null) {
            $result .= ' REQUIRE';
            if ($this->tlsOptions === []) {
                $result .= ' NONE';
            } else {
                $result .= ' ' . $formatter->formatSerializablesList($this->tlsOptions, ' AND ');
            }
        }
        if ($this->resourceOptions !== null) {
            $result .= ' WITH ' . $formatter->formatSerializablesList($this->resourceOptions, ' ');
        }
        if ($this->passwordLockOptions !== null) {
            $result .= ' ' . $formatter->formatSerializablesList($this->passwordLockOptions, ' ');
        }
        if ($this->comment !== null) {
            $result .= ' COMMENT ' . $formatter->formatString($this->comment);
        } elseif ($this->attribute !== null) {
            $result .= ' ATTRIBUTE ' . $formatter->formatString($this->attribute);
        }

        return $result;
    }

}
