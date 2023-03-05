<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Index;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\SqlSerializable;
use function ltrim;

class IndexOptions implements SqlSerializable
{

    private ?int $keyBlockSize;

    private ?string $withParser;

    private ?int $mergeThreshold;

    private ?string $comment;

    private ?bool $visible;

    private ?string $engineAttribute;

    private ?string $secondaryEngineAttribute;

    public function __construct(
        ?int $keyBlockSize,
        ?string $withParser,
        ?int $mergeThreshold,
        ?string $comment,
        ?bool $visible,
        ?string $engineAttribute,
        ?string $secondaryEngineAttribute
    )
    {
        $this->keyBlockSize = $keyBlockSize;
        $this->withParser = $withParser;
        $this->mergeThreshold = $mergeThreshold;
        $this->comment = $comment;
        $this->visible = $visible;
        $this->engineAttribute = $engineAttribute;
        $this->secondaryEngineAttribute = $secondaryEngineAttribute;
    }

    public function getKeyBlockSize(): ?int
    {
        return $this->keyBlockSize;
    }

    public function getParser(): ?string
    {
        return $this->withParser;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getMergeThreshold(): ?int
    {
        return $this->mergeThreshold;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function getEngineAttribute(): ?string
    {
        return $this->engineAttribute;
    }

    public function getSecondaryEngineAttribute(): ?string
    {
        return $this->secondaryEngineAttribute;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        if ($this->keyBlockSize !== null) {
            $result .= ' KEY_BLOCK_SIZE ' . $this->keyBlockSize;
        }
        if ($this->withParser !== null) {
            $result .= ' WITH PARSER ' . $formatter->formatName($this->withParser);
        }
        if ($this->comment !== null) {
            $result .= ' COMMENT ' . $formatter->formatString($this->comment);
        } elseif ($this->mergeThreshold !== null) {
            $result .= " COMMENT 'MERGE_THRESHOLD={$this->mergeThreshold}'";
        }
        if ($this->visible !== null) {
            $result .= ' ' . ($this->visible ? 'VISIBLE' : 'INVISIBLE');
        }
        if ($this->engineAttribute !== null) {
            $result .= ' ENGINE_ATTRIBUTE ' . $formatter->formatString($this->engineAttribute);
        }
        if ($this->secondaryEngineAttribute !== null) {
            $result .= ' SECONDARY_ENGINE_ATTRIBUTE ' . $formatter->formatString($this->secondaryEngineAttribute);
        }

        return ltrim($result);
    }

}
