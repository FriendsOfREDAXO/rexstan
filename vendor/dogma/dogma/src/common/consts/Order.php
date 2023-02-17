<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use const SORT_ASC;
use const SORT_DESC;

class Order
{
    use StaticClassMixin;

    public const ASCENDING = SORT_ASC; // 4
    public const DESCENDING = SORT_DESC; // 3

}
