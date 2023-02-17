<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

use Dogma\Enum\StringEnum;
use function strtolower;

class LocaleNumbers extends StringEnum
{

    public const ARABIC_INDIC = 'arab';
    public const ESTENDED_ARABIC_INDIC = 'arabext';
    public const ARMENIAN = 'armn';
    public const ARMENIAN_LOWERCASE = 'armnlow';
    public const BALINESE = 'bali';
    public const BENGALI = 'beng';
    public const DEVANAGARI = 'deva';
    public const ETHIOPIC = 'ethi';
    public const FINANCIAL = 'finance';
    public const FULL_WIDTH = 'fullwide';
    public const GEORGIAN = 'geor';
    public const GREEK = 'grek';
    public const GREEK_LOWERCASE = 'greklow';
    public const GUJARATI = 'gujr';
    public const GURMUKHI = 'guru';
    public const CHINESE_DECIMAL = 'hanidec';
    public const SIMPLIFIES_CHINESE = 'hans';
    public const SIMPLIFIES_CHINESE_FINANCIAL = 'hansfin';
    public const TRADITIONAL_CHINESE = 'hant';
    public const TRADITIONAL_CHINESE_FINANCIAL = 'hantfin';
    public const HEBREW = 'hebr';
    public const JAVANESE = 'java';
    public const JAPANESE = 'jpan';
    public const JAPANESE_FINANCIAL = 'jpanfin';
    public const KHMER = 'khmr';
    public const KANNADA = 'knda';
    public const LAO = 'laoo';
    public const WESTERN = 'latn';
    public const MALAYALAN = 'mlym';
    public const MONGOLIAN = 'mong';
    public const MYANMAR = 'mymr';
    public const NATIVE = 'native';
    public const ORIYA = 'orya';
    public const OSMANYA = 'osma';
    public const ROMAN = 'roman';
    public const ROMAN_LOWERCASE = 'romanlow';
    public const SAURASHTRA = 'saur';
    public const SUNDANESE = 'sund';
    public const TRADITIONAL_TAMIL = 'taml';
    public const TAMIL = 'tamldec';
    public const TELUGU = 'telu';
    public const THAI = 'thai';
    public const TIBETAN = 'tibt';
    public const TRADITIONAL = 'traditional';
    public const VAI = 'vaii';

    public static function validateValue(string &$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
