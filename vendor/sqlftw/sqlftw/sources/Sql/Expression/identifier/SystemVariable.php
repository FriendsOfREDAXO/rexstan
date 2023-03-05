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
use SqlFtw\Sql\MysqlVariable;
use function array_map;
use function explode;
use function implode;

/**
 * Variable name, e.g. VERSION
 */
class SystemVariable implements Identifier
{

    private string $name;

    private ?Scope $scope;

    public function __construct(string $name, ?Scope $scope = null)
    {
        if (!MysqlVariable::validateValue($name)) {
            throw new InvalidDefinitionException("Invalid system variable name '$name'.");
        }

        $this->name = $name;
        $this->scope = $scope;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScope(): ?Scope
    {
        return $this->scope;
    }

    public function getFullName(): string
    {
        return ($this->scope !== null ? '@@' . $this->scope->getValue() . '.' : '@@') . $this->name;
    }

    public function serialize(Formatter $formatter): string
    {
        $parts = array_map(static function (string $part) use ($formatter): string {
            return $formatter->getSession()->getPlatform()->isReserved($part) ? '`' . $part . '`' : $part;
        }, explode('.', $this->name));

        return ($this->scope !== null ? '@@' . $this->scope->getValue() . '.' : '@@') . implode('.', $parts);
    }

}
