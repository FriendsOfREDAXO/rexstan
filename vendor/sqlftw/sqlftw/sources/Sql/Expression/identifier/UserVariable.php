<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\InvalidDefinitionException;

/**
 * e.g. @foo
 */
class UserVariable implements Identifier
{

    private string $name;

    public function __construct(string $name)
    {
        if ($name[0] !== '@') {
            throw new InvalidDefinitionException('User variable name must start with "@".');
        }
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return $this->name;
    }

    public function serialize(Formatter $formatter): string
    {
        return $formatter->formatName($this->name);
    }

}
