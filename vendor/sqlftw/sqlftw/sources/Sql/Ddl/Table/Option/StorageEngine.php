<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: MROONGA MYROCKS Mroonga OQGRAPH Percona TOKUDB XTRADB columnstrore federatedx mroonga myrocks oqgraph tokudb xtradb

namespace SqlFtw\Sql\Ddl\Table\Option;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use function in_array;
use function strtolower;

/**
 * @see https://en.wikipedia.org/wiki/Comparison_of_MySQL_database_engines
 */
class StorageEngine implements SqlSerializable
{

    private string $value;

    public function __construct(string $value)
    {
        if (!isset(self::$map[strtolower($value)])) {
            throw new InvalidDefinitionException("Invalid storage engine name: $value.");
        }
        $this->value = self::$map[strtolower($value)];
    }

    // standard
    public const INNODB = 'InnoDB';
    public const MYISAM = 'MyISAM';
    public const MEMORY = 'Memory';
    public const CSV = 'CSV';
    public const ARCHIVE = 'Archive';
    public const BLACKHOLE = 'Blackhole';
    public const TEMP_TABLE = 'TempTable';
    public const PERFORMANCE_SCHEMA = 'performance_schema';

    // NDB
    public const NDB = 'NDB';
    public const NDBCLUSTER = 'ndbcluster';
    public const NDBINFO = 'ndbinfo';

    // HeatWave (secondary engine)
    public const RAPID = 'rapid';

    // Percona
    public const TOKUDB = 'TokuDB';
    public const XTRADB = 'XtraDB';

    // MariaDB
    public const ARIA = 'Aria';
    public const MARIA = 'Maria'; // ???
    public const CONNECT = 'Connect';
    public const FEDERATED_X = 'FederatedX';
    public const COLUMN_STORE = 'ColumnStore';
    public const MROONGA = 'Mroonga';
    public const MYROCKS = 'MyRocks';
    public const OQGRAPH = 'OQGRAPH';
    public const S3 = 'S3';
    public const SEQUENCE = 'SEQUENCE';
    public const SPHINX = 'Sphinx';
    public const SPIDER = 'SPIDER';

    // deprecated etc.
    public const BERKELEYDB = 'BerkeleyDB';
    public const MERGE = 'Merge';
    public const MRG_MYISAM = 'MRG_MyISAM';
    public const FEDERATED = 'Federated';
    public const FALCON = 'Falcon';
    public const HEAP = 'HEAP'; // old alias for Memory

    /** @var array<string, string> */
    private static array $map = [
        'archive' => self::ARCHIVE,
        'aria' => self::ARIA,
        'berkeleydb' => self::BERKELEYDB,
        'blackhole' => self::BLACKHOLE,
        'columnstore' => self::COLUMN_STORE,
        'csv' => self::CSV,
        'falcon' => self::FALCON,
        'federated' => self::FEDERATED,
        'federatedx' => self::FEDERATED_X,
        'heap' => self::HEAP,
        'innodb' => self::INNODB,
        'maria' => self::MARIA,
        'memory' => self::MEMORY,
        'merge' => self::MERGE,
        'mrg_myisam' => self::MRG_MYISAM,
        'mroonga' => self::MROONGA,
        'myisam' => self::MYISAM,
        'myrocks' => self::MYROCKS,
        'ndb' => self::NDB,
        'ndbcluster' => self::NDBCLUSTER,
        'ndbinfo' => self::NDBINFO,
        'oqgraph' => self::OQGRAPH,
        'performance_schema' => self::PERFORMANCE_SCHEMA,
        's3' => self::S3,
        'sequence' => self::SEQUENCE,
        'sphinx' => self::SPHINX,
        'spider' => self::SPIDER,
        'temptable' => self::TEMP_TABLE,
        'tokudb' => self::TOKUDB,
        'xtradb' => self::XTRADB,
    ];

    /** @var list<string> */
    private static array $transactional = [
        self::BERKELEYDB,
        self::FALCON,
        self::FEDERATED_X,
        self::COLUMN_STORE,
        self::INNODB,
        self::MYROCKS,
        self::NDB,
        self::NDBCLUSTER,
        self::SPIDER,
        self::TOKUDB,
        self::XTRADB,
    ];

    public function getValue(): string
    {
        return $this->value;
    }

    public function equalsValue(string $value): bool
    {
        $normalized = self::$map[strtolower($value)] ?? $value;

        return $this->value === $normalized;
    }

    public static function isValidValue(string $value): bool
    {
        return self::validateValue($value);
    }

    protected static function validateValue(string &$value): bool
    {
        $normalized = self::$map[strtolower($value)] ?? null;

        if ($normalized !== null) {
            $value = $normalized;
            return true;
        }

        return false;
    }

    public function transactional(): bool
    {
        return in_array($this->getValue(), self::$transactional, true);
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->value;
    }

}
