<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Sql\Expression\Value;

trait FunctionsBool
{

    /**
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function _and($left, $right): ?bool
    {
        $left = $this->cast->toBool($left);
        $right = $this->cast->toBool($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return $left && $right;
        }
    }

    /**
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function _or($left, $right): ?bool
    {
        $left = $this->cast->toBool($left);
        $right = $this->cast->toBool($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return $left || $right;
        }
    }

    /**
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function _xor($left, $right): ?bool
    {
        $left = $this->cast->toBool($left);
        $right = $this->cast->toBool($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return $left xor $right;
        }
    }

    /**
     * @param scalar|Value|null $right
     */
    public function _not($right): ?bool
    {
        $right = $this->cast->toBool($right);

        if ($right === null) {
            return null;
        } else {
            return !$right;
        }
    }

}
