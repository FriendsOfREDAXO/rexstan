<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Round
{
    use StaticClassMixin;

    public const NORMAL = 0;
    public const UP = 1;
    public const DOWN = 2;
    public const TOWARDS_ZERO = 3;
    public const AWAY_FROM_ZERO = 4;

}
