<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

// spell-check-ignore: big5han ducet DUCET eor EOR phonebook PHONEBOOK searchjl SEARCHJL UNIHAN unihan ZHUYIN zhuyin

namespace Dogma\Language\Locale;

use Dogma\Enum\StringEnum;
use function strtolower;

class LocaleCollation extends StringEnum
{

    public const BIG5 = 'big5han';
    public const DICTIONARY = 'dictionary';
    public const DUCET = 'ducet';
    public const EOR = 'eor';
    public const GB2312 = 'gb2312han';
    public const PHONEBOOK = 'phonebook';
    public const PHONETIC = 'phonetic';
    public const PINYIN = 'pinyin';
    public const REFORMED = 'reformed';
    public const SEARCH = 'search';
    public const SEARCHJL = 'searchjl';
    public const STANDARD = 'standard';
    public const STOKE = 'stroke';
    public const TRADITIONAL = 'traditional';
    public const UNIHAN = 'unihan';
    public const ZHUYIN = 'zhuyin';

    public static function validateValue(string &$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
