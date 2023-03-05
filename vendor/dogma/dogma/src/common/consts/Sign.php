<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Sign
{
    use StaticClassMixin;

    public const SIGNED = 'signed';
    public const UNSIGNED = 'unsigned';

    public const POSITIVE = 1;
    public const NEUTRAL = 0;
    public const NEGATIVE = -1;

}
