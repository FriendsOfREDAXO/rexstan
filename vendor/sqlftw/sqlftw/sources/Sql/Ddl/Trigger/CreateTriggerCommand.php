<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Trigger;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Ddl\UserExpression;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Expression\QualifiedName;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\StatementImpl;

class CreateTriggerCommand extends StatementImpl implements TriggerCommand
{

    private ObjectIdentifier $trigger;

    private TriggerEvent $event;

    private ObjectIdentifier $table;

    private Statement $body;

    private ?UserExpression $definer;

    private ?TriggerPosition $position;

    private bool $ifNotExists;

    public function __construct(
        ObjectIdentifier $trigger,
        TriggerEvent $event,
        ObjectIdentifier $table,
        Statement $body,
        ?UserExpression $definer = null,
        ?TriggerPosition $position = null,
        bool $ifNotExists = false
    ) {
        $this->trigger = $trigger;
        $this->event = $event;
        $this->table = $table;
        $this->body = $body;
        $this->definer = $definer;
        $this->position = $position;
        $this->ifNotExists = $ifNotExists;
    }

    public function getTrigger(): ObjectIdentifier
    {
        $schema = $this->table instanceof QualifiedName ? $this->table->getSchema() : null;

        return $schema !== null ? new QualifiedName($this->trigger->getName(), $schema) : $this->trigger;
    }

    public function getEvent(): TriggerEvent
    {
        return $this->event;
    }

    public function getTable(): ObjectIdentifier
    {
        return $this->table;
    }

    public function getBody(): Statement
    {
        return $this->body;
    }

    public function getDefiner(): ?UserExpression
    {
        return $this->definer;
    }

    public function getPosition(): ?TriggerPosition
    {
        return $this->position;
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
        $result .= ' TRIGGER ';
        if ($this->ifNotExists) {
            $result .= 'IF NOT EXISTS ';
        }
        $result .= $this->trigger->serialize($formatter) . ' ' . $this->event->serialize($formatter);
        $result .= ' ON ' . $this->table->serialize($formatter) . ' FOR EACH ROW';
        if ($this->position !== null) {
            $result .= ' ' . $this->position->serialize($formatter);
        }
        $result .= ' ' . $this->body->serialize($formatter);

        return $result;
    }

}
