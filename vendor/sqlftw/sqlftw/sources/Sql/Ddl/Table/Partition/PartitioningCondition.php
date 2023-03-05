<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Ddl\Table\Partition;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\SqlSerializable;

class PartitioningCondition implements SqlSerializable
{

    private PartitioningConditionType $type;

    private ?RootNode $expression;

    /** @var list<string>|null */
    private ?array $columns;

    private ?int $algorithm;

    /**
     * @param list<string>|null $columns
     */
    public function __construct(
        PartitioningConditionType $type,
        ?RootNode $expression,
        ?array $columns = null,
        ?int $algorithm = null
    ) {
        $this->type = $type;
        $this->expression = $expression;
        $this->columns = $columns;
        $this->algorithm = $algorithm;
    }

    public function getType(): PartitioningConditionType
    {
        return $this->type;
    }

    public function getExpression(): ?RootNode
    {
        return $this->expression;
    }

    /**
     * @return list<string>|null
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    public function getAlgorithm(): ?int
    {
        return $this->algorithm;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = $this->type->serialize($formatter);
        if ($this->expression !== null) {
            $result .= '(' . $this->expression->serialize($formatter) . ')';
        }
        if ($this->algorithm !== null) {
            $result .= ' ALGORITHM = ' . $this->algorithm . ' ';
        }
        if ($this->columns !== null) {
            if ($this->type->equalsAnyValue(PartitioningConditionType::RANGE, PartitioningConditionType::LIST)) {
                $result .= ' COLUMNS';
            }
            $result .= '(';
            if ($this->columns !== []) {
                $result .= $formatter->formatNamesList($this->columns);
            }
            $result .= ')';
        }

        return $result;
    }

}
