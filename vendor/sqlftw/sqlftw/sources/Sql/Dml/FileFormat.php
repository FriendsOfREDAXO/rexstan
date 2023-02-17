<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\SqlSerializable;

class FileFormat implements SqlSerializable
{

    private ?string $fieldsTerminatedBy;

    private ?string $fieldsEnclosedBy;

    private ?string $fieldsEscapedBy;

    private bool $optionallyEnclosed;

    private ?string $linesStaringBy;

    private ?string $linesTerminatedBy;

    public function __construct(
        ?string $fieldsTerminatedBy = null,
        ?string $fieldsEnclosedBy = null,
        ?string $fieldsEscapedBy = null,
        bool $optionallyEnclosed = false,
        ?string $linesStaringBy = null,
        ?string $linesTerminatedBy = null
    ) {
        $this->fieldsTerminatedBy = $fieldsTerminatedBy;
        $this->fieldsEnclosedBy = $fieldsEnclosedBy;
        $this->fieldsEscapedBy = $fieldsEscapedBy;
        $this->optionallyEnclosed = $optionallyEnclosed;
        $this->linesStaringBy = $linesStaringBy;
        $this->linesTerminatedBy = $linesTerminatedBy;
    }

    public function getFieldsTerminatedBy(): ?string
    {
        return $this->fieldsTerminatedBy;
    }

    public function getFieldsEnclosedBy(): ?string
    {
        return $this->fieldsEnclosedBy;
    }

    public function getFieldsEscapedBy(): ?string
    {
        return $this->fieldsEscapedBy;
    }

    public function getOptionallyEnclosed(): bool
    {
        return $this->optionallyEnclosed;
    }

    public function getLinesStartingBy(): ?string
    {
        return $this->linesStaringBy;
    }

    public function getLinesTerminatedBy(): ?string
    {
        return $this->linesTerminatedBy;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        if ($this->fieldsTerminatedBy !== null || $this->fieldsEnclosedBy !== null || $this->fieldsEscapedBy !== null) {
            $result .= ' FIELDS';
            if ($this->fieldsTerminatedBy !== null) {
                $result .= ' TERMINATED BY ' . $formatter->formatStringForceEscapeWhitespace($this->fieldsTerminatedBy);
            }
            if ($this->fieldsEnclosedBy !== null) {
                if ($this->optionallyEnclosed) {
                    $result .= ' OPTIONALLY';
                }
                $result .= ' ENCLOSED BY ' . $formatter->formatStringForceEscapeWhitespace($this->fieldsEnclosedBy);
            }
            if ($this->fieldsEscapedBy !== null) {
                $result .= ' ESCAPED BY ' . $formatter->formatStringForceEscapeWhitespace($this->fieldsEscapedBy);
            }
        }
        if ($this->linesStaringBy !== null || $this->linesTerminatedBy !== null) {
            $result .= ' LINES';
            if ($this->linesStaringBy !== null) {
                $result .= ' STARTING BY ' . $formatter->formatStringForceEscapeWhitespace($this->linesStaringBy);
            }
            if ($this->linesTerminatedBy !== null) {
                $result .= ' TERMINATED BY ' . $formatter->formatStringForceEscapeWhitespace($this->linesTerminatedBy);
            }
        }

        return $result;
    }

}
