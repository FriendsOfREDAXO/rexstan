<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Dml\Query;

use SqlFtw\Sql\SqlSerializable;

abstract class SelectInto implements SqlSerializable
{

    public const POSITION_BEFORE_FROM = 1;
    public const POSITION_BEFORE_LOCKING = 2;
    public const POSITION_AFTER_LOCKING = 3;

    /** @var self::POSITION_* */
    protected int $position;

    /**
     * @return self::POSITION_*
     */
    public function getPosition(): int
    {
        return $this->position;
    }

}
