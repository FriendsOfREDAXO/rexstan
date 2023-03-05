<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\Enum\IntEnum;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_SH;

class LockType extends IntEnum
{

    public const SHARED = LOCK_SH;
    public const EXCLUSIVE = LOCK_EX;
    public const NON_BLOCKING = LOCK_NB;

}
