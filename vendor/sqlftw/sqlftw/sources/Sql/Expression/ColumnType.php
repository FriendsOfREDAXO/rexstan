<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Collation;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;
use function count;
use function implode;
use function is_null;

/**
 * Column type used in CREATE TABLE definition and in routine arguments definitions
 */
class ColumnType implements SqlSerializable
{

    public const UNSIGNED = true;

    private BaseType $type;

    /** @var non-empty-list<int>|null */
    private ?array $size;

    /** @var non-empty-list<StringValue>|null */
    private ?array $values;

    private bool $unsigned;

    private ?Charset $charset;

    private ?Collation $collation;

    private ?int $srid;

    private bool $zerofill;

    /**
     * @param non-empty-list<int>|null $size
     * @param non-empty-list<StringValue>|null $values
     */
    public function __construct(
        BaseType $type,
        ?array $size = null,
        ?array $values = null,
        bool $unsigned = false,
        ?Charset $charset = null,
        ?Collation $collation = null,
        ?int $srid = null,
        bool $zerofill = false
    ) {
        if ($unsigned && !$type->isNumber()) {
            throw new InvalidDefinitionException("Non-numeric columns ({$type->getValue()}) cannot be unsigned.");
        }
        if ($zerofill && !$type->isNumber()) {
            throw new InvalidDefinitionException("Non-numeric columns ({$type->getValue()}) cannot be zerofill.");
        }
        if ($charset !== null && !$type->isText()) {
            throw new InvalidDefinitionException("Non-textual columns ({$type->getValue()}) cannot have charset.");
        }
        if ($collation !== null && !$type->isText()) {
            throw new InvalidDefinitionException("Non-textual columns ({$type->getValue()}) cannot have collation.");
        }
        if ($srid !== null && !$type->isSpatial()) {
            throw new InvalidDefinitionException("Non-spatial columns ({$type->getValue()}) cannot have srid.");
        }
        if ($values !== null && !$type->hasValues()) {
            throw new InvalidDefinitionException("Only enum and set columns can have list of values.");
        } elseif ($values === null && $type->hasValues()) {
            throw new InvalidDefinitionException("Enum and set columns must have list of values.");
        }
        $this->checkSize($type, $size);

        $this->type = $type;
        $this->size = $size;
        $this->values = $values;
        $this->unsigned = $unsigned;
        $this->charset = $charset;
        $this->collation = $collation;
        $this->srid = $srid;
        $this->zerofill = $zerofill;
    }

    /**
     * @param non-empty-list<int>|null $size
     */
    private function checkSize(BaseType $type, ?array $size): void
    {
        // phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.NoAssignment
        if ($type->isDecimal() || $type->equalsValue(BaseType::FLOAT)) {
            if ($size !== null && !(count($size) === 1 || count($size) === 2)) {
                throw new InvalidDefinitionException("One or two integer size parameters required for type {$type->getValue()}.");
            }
        } elseif ($type->isFloatingPointNumber()) {
            if ($size !== null && count($size) !== 2) {
                throw new InvalidDefinitionException("Two integer size parameters required for type {$type->getValue()}.");
            }
        } elseif ($type->isInteger() || $type->getValue() === BaseType::BIT) {
            if ($size !== null && count($size) !== 1) {
                throw new InvalidDefinitionException("One integer size parameter or null required for type {$type->getValue()}.");
            }
        } elseif ($type->needsLength()) {
            if ($size === null || count($size) !== 1) {
                throw new InvalidDefinitionException("One integer size parameter required for type {$type->getValue()}.");
            }
        } elseif ($type->hasLength()) {
            if ($size !== null && count($size) !== 1) {
                throw new InvalidDefinitionException("One integer size parameter required for type {$type->getValue()}.");
            }
        } elseif ($type->hasFsp()) {
            if (!is_null($size) && !(count($size) === 1 && $size[0] >= 0 && $size[0] <= 6)) {
                throw new InvalidDefinitionException("One integer size parameter in range from 0 to 6 required for type {$type->getValue()}.");
            }
        } elseif ($size !== null) {
            throw new InvalidDefinitionException("Type parameters do not match data type {$type->getValue()}.");
        }
    }

    public function getBaseType(): BaseType
    {
        return $this->type;
    }

    /**
     * @return non-empty-list<int>|null
     */
    public function getSize(): ?array
    {
        return $this->size;
    }

    /**
     * @param non-empty-list<int>|null $size
     */
    public function setSize(?array $size): void
    {
        $this->checkSize($this->type, $size);
        $this->size = $size;
    }

    /**
     * @return non-empty-list<StringValue>|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    public function setUnsigned(bool $unsigned): void
    {
        $this->unsigned = $unsigned;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function addCharset(Charset $charset): self
    {
        $that = clone $this;
        // todo: in fact it was collation all the time :E
        if ($this->charset !== null && $this->charset->equalsValue(Charset::BINARY)) {
            $that->collation = new Collation(Collation::BINARY);
        }
        $that->charset = $charset;

        return $that;
    }

    public function getCharset(): ?Charset
    {
        return $this->charset;
    }

    public function addCollation(Collation $collation): self
    {
        if ($this->collation !== null) {
            if ($this->collation->equals($collation) && $this->collation->equalsValue(Collation::BINARY)) {
                return $this;
            }
            throw new InvalidDefinitionException('Type already has a collation.');
        }

        $that = clone $this;
        $that->collation = $collation;

        return $that;
    }

    public function getCollation(): ?Collation
    {
        return $this->collation;
    }

    public function addSrid(int $srid): self
    {
        if ($this->srid !== null) {
            throw new InvalidDefinitionException('Type already has an srid.');
        }

        $that = clone $this;
        $that->srid = $srid;

        return $that;
    }

    public function getSrid(): ?int
    {
        return $this->srid;
    }

    public function zerofill(): bool
    {
        return $this->zerofill;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->type->serialize($formatter);

        if ($this->size !== null) {
            $result .= '(' . implode(', ', $this->size) . ')';
        } elseif ($this->values !== null) {
            $result .= '(' . $formatter->formatSerializablesList($this->values) . ')';
        }

        if ($this->unsigned === true) {
            $result .= ' UNSIGNED';
        }

        if ($this->zerofill) {
            $result .= ' ZEROFILL';
        }

        if ($this->charset !== null) {
            $result .= ' CHARACTER SET ' . $this->charset->serialize($formatter);
        }

        if ($this->collation !== null) {
            $result .= ' COLLATE ' . $this->collation->serialize($formatter);
        }

        if ($this->srid !== null) {
            $result .= ' SRID ' . $this->srid;
        }

        return $result;
    }

}
