<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

class SystemVariableInfo
{

    /** @readonly */
    public string $name;

    /** @readonly */
    public string $type;

    /** @readonly */
    public bool $nullable;

    /** @readonly */
    public bool $nonEmpty;

    /** @readonly */
    public bool $nonZero;

    /**
     * @readonly
     * @var non-empty-list<int|string>|null
     */
    public ?array $values;

    /**
     * @readonly
     * @var int|float|null
     */
    public $min;

    /**
     * @readonly
     * @var int|float|null
     */
    public $max;

    /** @readonly */
    public ?int $increment;

    /** @readonly */
    public bool $clamp;

    /** @readonly */
    public bool $clampMin;

    /**
     * @param non-empty-list<int|string>|null $values
     * @param int|float|null $min
     * @param int|float|null$max
     */
    public function __construct(
        string $name,
        string $type,
        bool $nullable,
        bool $nonEmpty,
        bool $nonZero,
        ?array $values,
        $min,
        $max,
        ?int $increment,
        bool $clamp,
        bool $clampMin
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->nonZero = $nonZero;
        $this->nonEmpty = $nonEmpty;
        $this->values = $values;
        $this->min = $min;
        $this->max = $max;
        $this->increment = $increment;
        $this->clamp = $clamp;
        $this->clampMin = $clampMin;
    }

}
