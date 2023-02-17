<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Enum;

use Dogma\Equalable;

interface Enum extends Equalable
{

    /**
     * @return string|int
     */
    public function getValue();

    /**
     * @param int|string $value
     * @return bool
     */
    public function equalsValue($value): bool;

}
