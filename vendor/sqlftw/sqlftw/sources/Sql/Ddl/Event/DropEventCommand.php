<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Event;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\StatementImpl;

class DropEventCommand extends StatementImpl implements EventCommand
{

    private ObjectIdentifier $event;

    private bool $ifExists;

    public function __construct(ObjectIdentifier $event, bool $ifExists = false)
    {
        $this->event = $event;
        $this->ifExists = $ifExists;
    }

    public function getEvent(): ObjectIdentifier
    {
        return $this->event;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP EVENT ';
        if ($this->ifExists) {
            $result .= 'IF EXISTS ';
        }
        $result .= $this->event->serialize($formatter);

        return $result;
    }

}
