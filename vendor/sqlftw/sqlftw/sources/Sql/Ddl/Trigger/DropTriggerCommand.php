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
use SqlFtw\Sql\Expression\ObjectIdentifier;
use SqlFtw\Sql\Statement;

class DropTriggerCommand extends Statement implements TriggerCommand
{

    private ObjectIdentifier $trigger;

    private bool $ifExists;

    public function __construct(ObjectIdentifier $trigger, bool $ifExists = false)
    {
        $this->trigger = $trigger;
        $this->ifExists = $ifExists;
    }

    public function getTrigger(): ObjectIdentifier
    {
        return $this->trigger;
    }

    public function ifExists(): bool
    {
        return $this->ifExists;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = 'DROP TRIGGER ';
        if ($this->ifExists) {
            $result .= 'IF EXISTS ';
        }
        $result .= $this->trigger->serialize($formatter);

        return $result;
    }

}
