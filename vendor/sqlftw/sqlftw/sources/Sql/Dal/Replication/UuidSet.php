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
use SqlFtw\Parser\Lexer;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use function array_map;
use function implode;
use function preg_match;
use function strtolower;

class UuidSet implements SqlSerializable
{

    private string $uuid;

    /** @var array<array<int, int|null>> */
    private array $intervals;

    /**
     * @param array<array<int, int|null>> $intervals
     */
    public function __construct(string $uuid, array $intervals)
    {
        $uuid = strtolower($uuid);
        if (preg_match(Lexer::UUID_REGEXP, $uuid) === 0) {
            throw new InvalidDefinitionException("Invalid UUID value '$uuid'.");
        }
        $this->uuid = $uuid;
        $this->intervals = $intervals;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return array<array<int, int|null>>
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->uuid . ':' . implode(':', array_map(static function (array $interval) {
            return $interval[0] . ($interval[1] !== null ? '-' . $interval[1] : '');
        }, $this->intervals));
    }

}
