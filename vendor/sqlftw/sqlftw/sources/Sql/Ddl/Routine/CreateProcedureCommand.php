<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Routine;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\SqlSecurity;
use SqlFtw\Sql\Ddl\UserExpression;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;
use function array_values;

class CreateProcedureCommand extends Statement implements StoredProcedureCommand, CreateRoutineCommand
{

    private ObjectIdentifier $procedure;

    private Statement $body;

    /** @var array<string, ProcedureParam> */
    private array $params;

    private ?UserExpression $definer;

    private ?bool $deterministic;

    private ?SqlSecurity $security;

    private ?RoutineSideEffects $sideEffects;

    private ?string $comment;

    private ?string $language;

    private bool $ifNotExists;

    /**
     * @param array<string, ProcedureParam> $params
     */
    public function __construct(
        ObjectIdentifier $procedure,
        Statement $body,
        array $params,
        ?UserExpression $definer = null,
        ?bool $deterministic = null,
        ?SqlSecurity $security = null,
        ?RoutineSideEffects $sideEffects = null,
        ?string $comment = null,
        ?string $language = null,
        bool $ifNotExists = false
    ) {
        $this->procedure = $procedure;
        $this->body = $body;
        $this->params = $params;
        $this->definer = $definer;
        $this->deterministic = $deterministic;
        $this->security = $security;
        $this->sideEffects = $sideEffects;
        $this->comment = $comment;
        $this->language = $language;
        $this->ifNotExists = $ifNotExists;
    }

    public function getProcedure(): ObjectIdentifier
    {
        return $this->procedure;
    }

    public function getBody(): Statement
    {
        return $this->body;
    }

    /**
     * @return array<string, ProcedureParam>
     */
    public function getParams(): array
    {
        return $this->params;
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
        $result .= ' PROCEDURE ';
        if ($this->ifNotExists) {
            $result .= 'IF NOT EXISTS ';
        }
        $result .= $this->procedure->serialize($formatter);

        $result .= '(';
        if ($this->params !== []) {
             $result .= $formatter->formatSerializablesList(array_values($this->params));
        }
        $result .= ')';

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
