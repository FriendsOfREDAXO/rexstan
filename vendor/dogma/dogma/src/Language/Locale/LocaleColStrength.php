<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

use Collator;
use Dogma\Enum\StringEnum;
use function strtolower;

class LocaleColStrength extends StringEnum implements LocaleCollationOption
{

    public const PRIMARY = 'primary';
    public const SECONDARY = 'secondary';
    public const TERTIARY = 'tertiary';
    public const QUATERNARY = 'quaternary';
    public const IDENTICAL = 'identical';

    public static function validateValue(string &$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

    public function getCollatorValue(): int
    {
        return [
            self::PRIMARY => Collator::PRIMARY,
            self::SECONDARY => Collator::SECONDARY,
            self::TERTIARY => Collator::TERTIARY,
            self::QUATERNARY => Collator::TERTIARY,
            self::IDENTICAL => Collator::IDENTICAL,
        ][$this->getValue()];
    }

}
