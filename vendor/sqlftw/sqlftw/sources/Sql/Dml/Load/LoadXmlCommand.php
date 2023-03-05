<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Load;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Assignment;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Dml\DuplicateOption;
use SqlFtw\Sql\Expression\ObjectIdentifier;

class LoadXmlCommand extends LoadCommand
{

    private ?string $rowsTag;

    /**
     * @param non-empty-list<string>|null $fields
     * @param non-empty-list<Assignment>|null $assignments
     */
    public function __construct(
        string $file,
        ObjectIdentifier $table,
        ?string $rowsTag = null,
        ?Charset $charset = null,
        ?array $fields = null,
        ?array $assignments = null,
        ?int $ignoreRows = null,
        ?LoadPriority $priority = null,
        bool $local = false,
        ?DuplicateOption $duplicateOption = null
    ) {
        parent::__construct($file, $table, $charset, $fields, $assignments, $ignoreRows, $priority, $local, $duplicateOption);

        $this->rowsTag = $rowsTag;
    }

    public function getRowsTag(): ?string
    {
        return $this->rowsTag;
    }

    protected function getWhat(): string
    {
        return 'XML';
    }

    protected function serializeFormat(Formatter $formatter): string
    {
        return $this->rowsTag !== null ? ' ROWS IDENTIFIED BY ' . $formatter->formatString($this->rowsTag) : '';
    }

}
