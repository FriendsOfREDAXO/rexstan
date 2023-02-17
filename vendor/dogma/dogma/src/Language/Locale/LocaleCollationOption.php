<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language\Locale;

use Dogma\Enum\StringEnum;

interface LocaleCollationOption
{

    /**
     * @return LocaleCollationOption
     */
    public static function get(string $value): StringEnum;

    public function getCollatorValue(): int;

}
