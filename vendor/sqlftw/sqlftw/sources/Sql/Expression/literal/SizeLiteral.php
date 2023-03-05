<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: kmg

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use function strlen;
use function strtolower;

/**
 * Size in bytes - e.g. 1024, 16k, 4M, 1G
 */
class SizeLiteral implements UintValue
{

    public const REGEXP = '~[1-9][0-9]*(kmg)?~i';

    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function asInt(): int
    {
        $base = (int) $this->value;
        $unit = strtolower($this->value[strlen($this->value) - 1]);

        if ($unit === 'g') {
            return $base * 1024 * 1024 * 1024;
        } elseif ($unit === 'm') {
            return $base * 1024 * 1024;
        } elseif ($unit === 'k') {
            return $base * 1024;
        } else {
            return $base;
        }
    }

    public function serialize(Formatter $formatter): string
    {
        return $this->value;
    }

}
