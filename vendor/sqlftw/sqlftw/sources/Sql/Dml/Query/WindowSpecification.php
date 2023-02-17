<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\OrderByExpression;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;
use function array_map;
use function implode;

class WindowSpecification implements SqlSerializable
{

    private ?string $name;

    /** @var non-empty-list<RootNode>|null */
    private ?array $partitionBy;

    /** @var non-empty-list<OrderByExpression>|null */
    private ?array $orderBy;

    private ?WindowFrame $frame;

    /**
     * @param non-empty-list<RootNode>|null $partitionBy
     * @param non-empty-list<OrderByExpression>|null $orderBy
     */
    public function __construct(?string $name, ?array $partitionBy, ?array $orderBy, ?WindowFrame $frame)
    {
        $this->name = $name;
        $this->partitionBy = $partitionBy;
        $this->orderBy = $orderBy;
        $this->frame = $frame;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return non-empty-list<RootNode>|null
     */
    public function getPartitionBy(): ?array
    {
        return $this->partitionBy;
    }

    /**
     * @return non-empty-list<OrderByExpression>|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    public function getFrame(): ?WindowFrame
    {
        return $this->frame;
    }

    public function serialize(Formatter $formatter): string
    {
        $parts = [];
        if ($this->name !== null) {
            $parts[] = $formatter->formatName($this->name);
        }
        if ($this->partitionBy !== null) {
            $parts[] = 'PARTITION BY ' . implode(', ', array_map(static function (RootNode $expression) use ($formatter): string {
                return $expression->serialize($formatter);
            }, $this->partitionBy));
        }
        if ($this->orderBy !== null) {
            $parts[] = 'ORDER BY ' . implode(', ', array_map(static function (OrderByExpression $expression) use ($formatter): string {
                return $expression->serialize($formatter);
            }, $this->orderBy));
        }
        if ($this->frame !== null) {
            $parts[] = $this->frame->serialize($formatter);
        }

        return '(' . implode(' ', $parts) . ')';
    }

}
