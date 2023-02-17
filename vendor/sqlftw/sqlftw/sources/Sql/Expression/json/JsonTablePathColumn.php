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

class JsonTablePathColumn implements JsonTableColumn
{

    private string $name;

    private ColumnType $type;

    private StringValue $path;

    private ?JsonErrorCondition $onEmpty;

    private ?JsonErrorCondition $onError;

    public function __construct(
        string $name,
        ColumnType $type,
        StringValue $path,
        ?JsonErrorCondition $onEmpty = null,
        ?JsonErrorCondition $onError = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->path = $path;
        $this->onEmpty = $onEmpty;
        $this->onError = $onError;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ColumnType
    {
        return $this->type;
    }

    public function getPath(): StringValue
    {
        return $this->path;
    }

    public function getOnEmpty(): ?JsonErrorCondition
    {
        return $this->onEmpty;
    }

    public function getOnError(): ?JsonErrorCondition
    {
        return $this->onError;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->name . ' ' . $this->type->serialize($formatter) . ' PATH ' . $this->path->serialize($formatter);

        if ($this->onEmpty !== null) {
            $result .= ' ' . $this->onEmpty->serialize($formatter) . ' ON EMPTY';
        }
        if ($this->onError !== null) {
            $result .= ' ' . $this->onError->serialize($formatter) . ' ON ERROR';
        }

        return $result;
    }

}
