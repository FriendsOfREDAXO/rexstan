<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Event;

use SqlFtw\Sql\Ddl\UserExpression;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;

class EventDefinition
{

    private ObjectIdentifier $event;

    private EventSchedule $schedule;

    private Statement $body;

    private ?UserExpression $definer;

    private ?EventState $state;

    private ?bool $preserve;

    private ?string $comment;

    public function __construct(
        ObjectIdentifier $event,
        EventSchedule $schedule,
        Statement $body,
        ?UserExpression $definer = null,
        ?EventState $state = null,
        ?bool $preserve = null,
        ?string $comment = null
    ) {
        $this->event = $event;
        $this->schedule = $schedule;
        $this->body = $body;
        $this->definer = $definer;
        $this->state = $state;
        $this->preserve = $preserve;
        $this->comment = $comment;
    }

    public function alter(AlterEventCommand $alter): self
    {
        $that = clone $this;

        $that->schedule = $alter->getSchedule() ?? $that->schedule;
        $that->body = $alter->getBody() ?? $that->body;
        $that->definer = $alter->getDefiner() ?? $that->definer;
        $that->state = $alter->getState() ?? $that->state;
        $that->preserve = $alter->preserve() ?? $that->preserve;
        $that->comment = $alter->getComment() ?? $that->comment;
        $that->event = $alter->getNewName() ?? $that->event;

        return $that;
    }

    public function getEvent(): ObjectIdentifier
    {
        return $this->event;
    }

    public function getSchedule(): EventSchedule
    {
        return $this->schedule;
    }

    public function getBody(): Statement
    {
        return $this->body;
    }

    public function getDefiner(): ?UserExpression
    {
        return $this->definer;
    }

    public function getState(): ?EventState
    {
        return $this->state;
    }

    public function preserve(): ?bool
    {
        return $this->preserve;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

}
