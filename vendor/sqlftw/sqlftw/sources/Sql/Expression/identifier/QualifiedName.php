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
use SqlFtw\Sql\Dml\OptimizerHint\HintTableIdentifier;

/**
 * Name of an object including schema name, e.g. "foo.bar"
 */
class QualifiedName implements ColumnIdentifier, FunctionIdentifier, ObjectIdentifier, HintTableIdentifier
{

    private string $name;

    private string $schema;

    public function __construct(string $name, string $schema)
    {
        $this->name = $name;
        $this->schema = $schema;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return $this->schema . '.' . $this->name;
    }

    /**
     * @return array{string, string}
     */
    public function toArray(): array
    {
        return [$this->name, $this->schema];
    }

    public function equals(string $fullName): bool
    {
        return $fullName === $this->schema . '.' . $this->name;
    }

    public function serialize(Formatter $formatter): string
    {
        return $formatter->formatName($this->schema) . '.' . $formatter->formatName($this->name);
    }

}
