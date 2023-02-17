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
use SqlFtw\Sql\Ddl\Table\Constraint\ConstraintBody;
use SqlFtw\Sql\Ddl\Table\TableItem;
use SqlFtw\Sql\Expression\ObjectIdentifier;
use function count;

class IndexDefinition implements TableItem, ConstraintBody
{

    public const PRIMARY_KEY_NAME = null;

    private ?string $name;

    private IndexType $type;

    /** @var non-empty-list<IndexPart> */
    private array $parts;

    private ?IndexAlgorithm $algorithm;

    private ?IndexOptions $options;

    private ?ObjectIdentifier $table;

    /**
     * @param non-empty-list<IndexPart> $parts
     */
    public function __construct(
        ?string $name,
        IndexType $type,
        array $parts,
        ?IndexAlgorithm $algorithm = null,
        ?IndexOptions $options = null,
        ?ObjectIdentifier $table = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->parts = $parts;
        $this->algorithm = $algorithm;
        $this->options = $options;
        $this->table = $table;
    }

    public function duplicateAsPrimary(): self
    {
        $self = clone $this;
        $self->type = new IndexType(IndexType::PRIMARY);
        $self->name = self::PRIMARY_KEY_NAME;

        return $self;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): IndexType
    {
        return $this->type;
    }

    public function isPrimary(): bool
    {
        return $this->type->getValue() === IndexType::PRIMARY;
    }

    public function isUnique(): bool
    {
        return $this->type->getValue() === IndexType::UNIQUE;
    }

    public function isMultiColumn(): bool
    {
        return count($this->parts) > 1;
    }

    public function getAlgorithm(): ?IndexAlgorithm
    {
        return $this->algorithm;
    }

    public function getOptions(): ?IndexOptions
    {
        return $this->options;
    }

    public function getTable(): ?ObjectIdentifier
    {
        return $this->table;
    }

    /**
     * @return non-empty-list<IndexPart>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->serializeHead($formatter) . ' ' . $this->serializeTail($formatter);
    }

    public function serializeHead(Formatter $formatter): string
    {
        $result = $this->type->serialize($formatter);

        if ($this->name !== null) {
            $result .= ' ' . $formatter->formatName($this->name);
        }

        return $result;
    }

    public function serializeTail(Formatter $formatter): string
    {
        $result = '(' . $formatter->formatSerializablesList($this->parts) . ')';

        if ($this->algorithm !== null) {
            $result .= ' USING ' . $this->algorithm->serialize($formatter);
        }

        if ($this->options !== null) {
            $options = $this->options->serialize($formatter);
            if ($options !== '') {
                $result .= ' ' . $options;
            }
        }

        return $result;
    }

}
