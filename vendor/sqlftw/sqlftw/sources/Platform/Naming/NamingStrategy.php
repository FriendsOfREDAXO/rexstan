<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Platform\Naming;

use SqlFtw\Sql\Expression\QualifiedName;

interface NamingStrategy
{

    /**
     * @param non-empty-list<string> $columns
     * @param list<string> $existingIndexes
     */
    public function createIndexName(QualifiedName $table, array $columns, array $existingIndexes = []): string;

    /**
     * @param non-empty-list<string> $columns
     * @param list<string> $existingKeys
     */
    public function createForeignKeyName(QualifiedName $table, array $columns, array $existingKeys = []): string;

    /**
     * @param non-empty-list<string> $columns
     * @param list<string> $existingChecks
     */
    public function createCheckName(QualifiedName $table, array $columns, array $existingChecks = []): string;

}
