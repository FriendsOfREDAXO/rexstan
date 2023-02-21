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
use SqlFtw\Sql\Dml\FileFormat;
use SqlFtw\Sql\Expression\ObjectIdentifier;

class LoadDataCommand extends LoadCommand
{

    private ?FileFormat $format;

    /**
     * @param non-empty-list<string>|null $fields
     * @param non-empty-list<Assignment>|null $assignments
     * @param non-empty-list<string>|null $partitions
     */
    public function __construct(
        string $file,
        ObjectIdentifier $table,
        ?FileFormat $format,
        ?Charset $charset = null,
        ?array $fields = null,
        ?array $assignments = null,
        ?int $ignoreRows = null,
        ?LoadPriority $priority = null,
        bool $local = false,
        ?DuplicateOption $duplicateOption = null,
        ?array $partitions = null
    ) {
        parent::__construct($file, $table, $charset, $fields, $assignments, $ignoreRows, $priority, $local, $duplicateOption, $partitions);

        $this->format = $format;
    }

    public function getFormat(): ?FileFormat
    {
        return $this->format;
    }

    protected function getWhat(): string
    {
        return 'DATA';
    }

    protected function serializeFormat(Formatter $formatter): string
    {
        return $this->format !== null ? $this->format->serialize($formatter) : '';
    }

}
