<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Routine;

use Dogma\Arr;
use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\SqlSecurity;
use SqlFtw\Sql\Ddl\UserExpression;
use SqlFtw\Sql\Expression\ColumnType;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;
use function implode;

class CreateFunctionCommand extends Statement implements StoredFunctionCommand, CreateRoutineCommand
{

    private ObjectIdentifier $function;

    private Statement $body;

    /** @var array<string, ColumnType> ($name => $type) */
    private array $params;

    private ColumnType $returnType;

    private ?UserExpression $definer;

    private ?bool $deterministic;

    private ?SqlSecurity $security;

    private ?RoutineSideEffects $sideEffects;

    private ?string $comment;

    private ?string $language;

    private bool $ifNotExists;

    /**
     * @param array<string, ColumnType> $params ($name => $type)
     */
    public function __construct(
        ObjectIdentifier $function,
        Statement $body,
        array $params,
        ColumnType $returnType,
        ?UserExpression $definer = null,
        ?bool $deterministic = null,
        ?SqlSecurity $security = null,
        ?RoutineSideEffects $sideEffects = null,
        ?string $comment = null,
        ?string $language = null,
        bool $ifNotExists = false
    ) {
        $this->function = $function;
        $this->body = $body;
        $this->params = $params;
        $this->returnType = $returnType;
        $this->definer = $definer;
        $this->deterministic = $deterministic;
        $this->security = $security;
        $this->sideEffects = $sideEffects;
        $this->comment = $comment;
        $this->language = $language;
        $this->ifNotExists = $ifNotExists;
    }

    public function getFunction(): ObjectIdentifier
    {
        return $this->function;
    }

    public function getBody(): Statement
    {
        return $this->body;
    }

    /**
     * @return array<string, ColumnType> ($name => $type)
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getReturnType(): ColumnType
    {
        return $this->returnType;
    }

    public function getDefiner(): ?UserExpression
    {
        return $this->definer;
    }

    public function isDeterministic(): ?bool
    {
        return $this->deterministic;
    }

    public function getSecurity(): ?SqlSecurity
    {
        return $this->security;
    }

    public function getSideEffects(): ?RoutineSideEffects
    {
        return $this->sideEffects;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function ifNotExists(): bool
    {
        return $this->ifNotExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'CREATE';
        if ($this->definer !== null) {
            $result .= ' DEFINER = ' . $this->definer->serialize($formatter);
        }
        $result .= ' FUNCTION ';
        if ($this->ifNotExists) {
            $result .= 'IF NOT EXISTS ';
        }
        $result .= $this->function->serialize($formatter);

        $result .= '(' . implode(', ', Arr::mapPairs($this->params, static function (string $name, ColumnType $type) use ($formatter) {
            return $formatter->formatName($name) . ' ' . $type->serialize($formatter);
        })) . ')';
        $result .= ' RETURNS ' . $this->returnType->serialize($formatter);

        if ($this->language !== null) {
            $result .= ' LANGUAGE ' . $this->language;
        }
        if ($this->deterministic !== null) {
            $result .= $this->deterministic ? ' DETERMINISTIC' : ' NOT DETERMINISTIC';
        }
        if ($this->sideEffects !== null) {
            $result .= ' ' . $this->sideEffects->serialize($formatter);
        }
        if ($this->security !== null) {
            $result .= ' SQL SECURITY ' . $this->security->serialize($formatter);
        }
        if ($this->comment !== null) {
            $result .= ' COMMENT ' . $formatter->formatString($this->comment);
        }

        $result .= ' ' . $this->body->serialize($formatter);

        return $result;
    }

}
