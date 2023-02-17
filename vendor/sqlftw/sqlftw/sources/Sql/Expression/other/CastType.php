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
use function count;
use function implode;
use function is_null;

/**
 * e.g. CAST(expr AS type)
 */
class CastType implements ArgumentNode, ArgumentValue
{

    public const UNSIGNED = true;

    private ?BaseType $type;

    /** @var non-empty-list<int>|null */
    private ?array $size;

    private ?bool $sign;

    private bool $array;

    private ?Charset $charset;

    private ?Collation $collation;

    private ?int $srid;

    /**
     * @param non-empty-list<int>|null $size
     */
    public function __construct(
        ?BaseType $type,
        ?array $size = null,
        ?bool $sign = null,
        bool $array = false,
        ?Charset $charset = null,
        ?Collation $collation = null,
        ?int $srid = null
    ) {
        if ($type === null && $sign !== null) {
            $type = new BaseType($sign ? BaseType::SIGNED : BaseType::UNSIGNED);
        }
        if ($type !== null) {
            if (isset($sign) && !$type->isInteger()) {
                throw new InvalidDefinitionException("Non-numeric types ({$type->getValue()}) cannot be signed/unsigned.");
            }
            if ($charset !== null && !$type->isText()) {
                throw new InvalidDefinitionException("Non-textual types ({$type->getValue()}) cannot have charset.");
            }
            if ($collation !== null && !$type->isText()) {
                throw new InvalidDefinitionException("Non-textual types ({$type->getValue()}) cannot have collation.");
            }
            if ($srid !== null && !$type->isSpatial()) {
                throw new InvalidDefinitionException("Non-spatial types ({$type->getValue()}) cannot have srid.");
            }
            $this->checkSize($type, $size);
        } elseif ($size !== null || $charset !== null || $collation !== null || $srid !== null) {
            throw new InvalidDefinitionException('Only sign and array options are allowed when BaseType is not defined.');
        }
        if ($array && $type !== null && $type->isSpatial()) {
            throw new InvalidDefinitionException('Geometry array is not supported.');
        }

        $this->type = $type;
        $this->size = $size;
        $this->sign = $sign;
        $this->array = $array;
        $this->charset = $charset;
        $this->collation = $collation;
        $this->srid = $srid;
    }

    /**
     * @param non-empty-list<int>|null $size
     */
    private function checkSize(BaseType $type, ?array $size): void
    {
        // phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.NoAssignment
        if ($type->isDecimal()) {
            if ($size !== null && !(count($size) === 1 || count($size) === 2)) {
                throw new InvalidDefinitionException("One or two integer size parameters required for type {$type->getValue()}.");
            }
        } elseif ($type->isFloatingPointNumber()) {
            if ($size !== null && count($size) !== 1) {
                throw new InvalidDefinitionException("One integer size parameters required for type {$type->getValue()}.");
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

    public function getBaseType(): ?BaseType
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

    public function getSign(): ?bool
    {
        return $this->sign;
    }

    public function isArray(): bool
    {
        return $this->array;
    }

    public function getCharset(): ?Charset
    {
        return $this->charset;
    }

    public function getCollation(): ?Collation
    {
        return $this->collation;
    }

    public function getSrid(): ?int
    {
        return $this->srid;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        $printSign = $this->sign !== null && $this->type !== null && !$this->type->equalsAnyValue(BaseType::SIGNED, BaseType::UNSIGNED);
        if ($printSign) {
            $result .= $this->sign === true ? 'SIGNED' : 'UNSIGNED';
        }
        if ($printSign && $this->type !== null) {
            $result .= ' ';
        }
        if ($this->type !== null) {
            $result .= $this->type->serialize($formatter);
        }

        if ($this->size !== null) {
            $result .= '(' . implode(', ', $this->size) . ')';
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

        if ($this->array) {
            $result .= ' ARRAY';
        }

        return $result;
    }

}
