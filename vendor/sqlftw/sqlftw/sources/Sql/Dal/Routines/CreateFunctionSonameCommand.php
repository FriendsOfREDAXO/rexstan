<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Dal\DalCommand;
use SqlFtw\Sql\Ddl\Routine\UdfReturnDataType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;

class CreateFunctionSonameCommand extends Statement implements DalCommand
{

    private ObjectIdentifier $function;

    private string $libName;

    private UdfReturnDataType $returnType;

    private bool $aggregate;

    public function __construct(ObjectIdentifier $function, string $libName, UdfReturnDataType $returnType, bool $aggregate)
    {
        $this->function = $function;
        $this->libName = $libName;
        $this->returnType = $returnType;
        $this->aggregate = $aggregate;
    }

    public function getFunction(): ObjectIdentifier
    {
        return $this->function;
    }

    public function getLibName(): string
    {
        return $this->libName;
    }

    public function getReturnType(): UdfReturnDataType
    {
        return $this->returnType;
    }

    public function isAggregate(): bool
    {
        return $this->aggregate;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CREATE ';
        if ($this->aggregate) {
            $result .= 'AGGREGATE ';
        }

        $result .= 'FUNCTION ' . $this->function->serialize($formatter)
            . ' RETURNS ' . $this->returnType->serialize($formatter)
            . ' SONAME ' . $formatter->formatString($this->libName);

        return $result;
    }

}
