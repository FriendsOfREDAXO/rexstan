<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\Expression\Value;

trait FunctionsInfo
{

    /**
     * BENCHMARK() - Repeatedly execute an expression
     *
     * @param scalar|Value|null $count
     * @param scalar|Value|null $expression
     */
    public function benchmark($count, $expression): ?int
    {
        $count = $this->cast->toInt($count);

        return $count === null || $count < 1 ? null : 0;
    }

    /**
     * CHARSET() - Return the character set of the argument
     *
     * @param scalar|Value|null $string
     */
    public function charset($string): string
    {
        return Charset::UTF8MB4; // arbitrary
    }

    /**
     * COERCIBILITY() - Return the collation coercibility value of the string argument
     *
     * @param scalar|Value|null $expression
     */
    public function coercibility($expression): int
    {
        return 0; // arbitrary
    }

    /**
     * COLLATION() - Return the collation of the string argument
     *
     * @param scalar|Value|null $string
     */
    public function collation($string): string
    {
        return Collation::UTF8MB4_GENERAL_0900_AS_CI; // arbitrary
    }

    /**
     * CONNECTION_ID() - Return the connection ID (thread ID) for the connection
     */
    public function connection_id(): int
    {
        return 1; // arbitrary
    }

    /**
     * CURRENT_ROLE() - Return the current active roles
     */
    public function current_role(): string
    {
        return ''; // arbitrary
    }

    /**
     * CURRENT_USER(), CURRENT_USER - The authenticated user name and host name
     */
    public function current_user(): string
    {
        return 'root@%'; // arbitrary
    }

    /**
     * DATABASE() - Return the default (current) database name
     */
    public function database(): ?string
    {
        return $this->session->getSchema();
    }

    /**
     * FOUND_ROWS() - For a SELECT with a LIMIT clause, the number of rows that would be returned were there no LIMIT clause
     */
    public function found_rows(): int
    {
        return 0; // arbitrary
    }

    /**
     * ICU_VERSION() - ICU library version
     */
    public function icu_version(): string
    {
        return '66.1'; // arbitrary, from MySQL 8.0.30
    }

    /**
     * LAST_INSERT_ID() - Value of the AUTOINCREMENT column for the last INSERT
     */
    public function last_insert_id(): int
    {
        return 1; // arbitrary
    }

    /**
     * ROLES_GRAPHML() - Return a GraphML document representing memory role subgraphs
     */
    public function roles_graphml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><graphml />'; // arbitrary, empty roles
    }

    /**
     * ROW_COUNT() - The number of rows updated
     */
    public function row_count(): int
    {
        return 0; // arbitrary
    }

    /**
     * SCHEMA() - Synonym for DATABASE()
     */
    public function schema(): ?string
    {
        return $this->session->getSchema();
    }

    /**
     * SESSION_USER() - Synonym for USER()
     */
    public function session_user(): string
    {
        return 'root@%'; // arbitrary
    }

    /**
     * SYSTEM_USER() - Synonym for USER()
     */
    public function system_user(): string
    {
        return 'root@%'; // arbitrary
    }

    /**
     * USER() - The user name and host name provided by the client
     */
    public function user(): string
    {
        return 'root@%'; // arbitrary
    }

    /**
     * VERSION() - Return a string that indicates the MySQL server version
     */
    public function version(): string
    {
        return $this->platform->getVersion()->format();
    }

}
