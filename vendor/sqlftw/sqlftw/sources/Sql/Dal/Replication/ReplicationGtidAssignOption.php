<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dal\Replication;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlSerializable;

class ReplicationGtidAssignOption implements SqlSerializable
{

    public const OFF = Keyword::OFF;
    public const LOCAL = Keyword::LOCAL;
    public const UUID = 'UUID';

    private string $type;

    private ?string $uuid;

    public function __construct(string $type, ?string $uuid = null)
    {
        if (!($type === self::OFF || $type === self::LOCAL || $type === self::UUID)) {
            throw new InvalidDefinitionException("Invalid type $type of replication GTID option.");
        } elseif (($type === self::UUID && $uuid === null) || ($type !== self::UUID && $uuid !== null)) {
            throw new InvalidDefinitionException("UUID must be set when GTID option type is 'UUID'.");
        }

        $this->type = $type;
        $this->uuid = $uuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->uuid !== null ? $formatter->formatString($this->uuid) : $this->type;
    }

}
