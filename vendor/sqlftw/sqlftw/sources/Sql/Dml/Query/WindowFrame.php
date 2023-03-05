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
use SqlFtw\Sql\Expression\RootNode;
use SqlFtw\Sql\InvalidDefinitionException;
use SqlFtw\Sql\SqlSerializable;

class WindowFrame implements SqlSerializable
{

    public WindowFrameUnits $units;

    public WindowFrameType $startType;

    public ?WindowFrameType $endType;

    public ?RootNode $startExpression;

    public ?RootNode $endExpression;

    public function __construct(
        WindowFrameUnits $units,
        WindowFrameType $startType,
        ?WindowFrameType $endType,
        ?RootNode $startExpression,
        ?RootNode $endExpression
    )
    {
        if ($startType->equalsAnyValue(WindowFrameType::PRECEDING, WindowFrameType::FOLLOWING) xor $startExpression !== null) {
            throw new InvalidDefinitionException('Expression must be provided if and only if frame start type is PRECEDING or FOLLOWING.');
        }

        if (($endType !== null && $endType->equalsAnyValue(WindowFrameType::PRECEDING, WindowFrameType::FOLLOWING)) xor $endExpression !== null) {
            throw new InvalidDefinitionException('Expression must be provided if and only if frame end type is PRECEDING or FOLLOWING.');
        }

        $this->units = $units;
        $this->startType = $startType;
        $this->endType = $endType;
        $this->startExpression = $startExpression;
        $this->endExpression = $endExpression;
    }

    public function getUnits(): WindowFrameUnits
    {
        return $this->units;
    }

    public function getStartType(): WindowFrameType
    {
        return $this->startType;
    }

    public function getEndType(): ?WindowFrameType
    {
        return $this->endType;
    }

    public function getStartExpression(): ?RootNode
    {
        return $this->startExpression;
    }

    public function getEndExpression(): ?RootNode
    {
        return $this->endExpression;
    }

    public function serialize(Formatter $formatter): string
    {
        $units = $this->units->serialize($formatter) . ' ';

        $result = $this->startExpression !== null
            ? $this->startExpression->serialize($formatter) . ' ' . $this->startType->serialize($formatter)
            : $this->startType->serialize($formatter);

        if ($this->endType !== null) {
            $result = 'BETWEEN ' . $result . ' AND ';
            $result .= $this->endExpression !== null
                ? $this->endExpression->serialize($formatter) . ' ' . $this->endType->serialize($formatter)
                : $this->endType->serialize($formatter);
        }

        return $units . $result;
    }

}
