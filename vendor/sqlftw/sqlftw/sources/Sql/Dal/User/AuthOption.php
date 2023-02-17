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
use SqlFtw\Sql\Expression\StringValue;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;

class AuthOption implements SqlSerializable
{

    private ?string $authPlugin;

    /** @var StringValue|false|null */
    private $password;

    private ?StringValue $as;

    private ?AuthOption $initial;

    private bool $oldHashedPassword;

    /**
     * @param StringValue|false|null $password
     */
    public function __construct(
        ?string $authPlugin,
        $password = null,
        ?StringValue $as = null,
        ?AuthOption $initial = null,
        bool $oldHashedPassword = false
    ) {
        if ($password !== null && $as !== null) {
            throw new InvalidDefinitionException('Only one of $password and $as can be set.');
        } elseif ($authPlugin === null && $as !== null) {
            throw new InvalidDefinitionException('When $as is set, $authPlugin must be set.');
        } elseif ($initial !== null && ($authPlugin === null || $password !== null || $as !== null)) {
            throw new InvalidDefinitionException('When $initial is set, $authPlugin must be set and $password and $as must not be set.');
        } elseif ($oldHashedPassword && ($password === null || $as !== null || $initial !== null || $authPlugin !== null)) {
            throw new InvalidDefinitionException('Only $password must be set when using $oldHashedPassword.');
        }

        $this->authPlugin = $authPlugin;
        $this->password = $password;
        $this->as = $as;
        $this->initial = $initial;
        $this->oldHashedPassword = $oldHashedPassword;
    }

    public function getAuthPlugin(): ?string
    {
        return $this->authPlugin;
    }

    /**
     * @return StringValue|false|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getAs(): ?StringValue
    {
        return $this->as;
    }

    public function getInitial(): ?AuthOption
    {
        return $this->initial;
    }

    public function oldHashedPassword(): bool
    {
        return $this->oldHashedPassword;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'IDENTIFIED ';
        if ($this->authPlugin !== null) {
            $result .= 'WITH ' . $formatter->formatString($this->authPlugin);
        }
        if ($this->password !== null) {
            $space = $this->authPlugin !== null ? ' ' : '';
            $result .= $space . 'BY ';
            if ($this->oldHashedPassword) {
                $result .= 'PASSWORD ';
            }
            $result .= ($this->password === false ? 'RANDOM PASSWORD' : $this->password->serialize($formatter));
        }
        if ($this->as !== null) {
            $result .= ' AS ' . $this->as->serialize($formatter);
        }
        if ($this->initial !== null) {
            $result .= ' INITIAL AUTHENTICATION ' . $this->initial->serialize($formatter);
        }

        return $result;
    }

}
