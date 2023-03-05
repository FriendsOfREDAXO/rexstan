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

/**
 * {NULL | DEFAULT json_string | ERROR} ON ...
 */
class JsonErrorCondition implements ArgumentNode
{

    public const NULL = true;
    public const ERROR = false;

    /** @var Literal|bool */
    private $value;

    /**
     * @param Literal|bool $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->value instanceof Literal
            ? 'DEFAULT ' . $this->value->serialize($formatter)
            : ($this->value ? 'NULL' : 'ERROR');
    }

}
