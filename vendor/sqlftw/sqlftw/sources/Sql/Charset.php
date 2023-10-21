<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use SqlFtw\Sql\Expression\ArgumentNode;
use SqlFtw\Sql\Expression\ArgumentValue;
use function array_search;
use function explode;
use function strpos;
use function strtolower;

class Charset extends SqlEnum implements ArgumentNode, ArgumentValue
{

    public const ARMSCII8 = 'armscii8';
    public const ASCII = 'ascii';
    public const BIG5 = 'big5';
    public const BINARY = 'binary';
    public const CP1250 = 'cp1250';
    public const CP1251 = 'cp1251';
    public const CP1256 = 'cp1256';
    public const CP1257 = 'cp1257';
    public const CP850 = 'cp850';
    public const CP852 = 'cp852';
    public const CP866 = 'cp866';
    public const CP932 = 'cp932';
    public const DEC8 = 'dec8';
    public const EUCJPMS = 'eucjpms';
    public const EUCKR = 'euckr';
    public const GB18030 = 'gb18030';
    public const GB2312 = 'gb2312';
    public const GBK = 'gbk';
    public const GEOSTD8 = 'geostd8';
    public const GREEK = 'greek';
    public const HEBREW = 'hebrew';
    public const HP8 = 'hp8';
    public const KEYBCS2 = 'keybcs2';
    public const KOI8R = 'koi8r';
    public const KOI8U = 'koi8u';
    public const LATIN1 = 'latin1'; // ISO-8859-1
    public const LATIN2 = 'latin2'; // ISO-8859-2
    public const LATIN5 = 'latin5'; // ISO-8859-9
    public const LATIN7 = 'latin7'; // ISO-8859-13
    public const MACCE = 'macce';
    public const MACLATIN2 = 'mac_latin2'; // alias for macroman
    public const MACROMAN = 'macroman';
    public const SJIS = 'sjis';
    public const SWE7 = 'swe7';
    public const TIS620 = 'tis620';
    public const UJIS = 'ujis';

    public const UNICODE = 'unicode'; // old alias for ucs2
    public const UCS2 = 'ucs2';
    public const UTF16 = 'utf16';
    public const UTF16LE = 'utf16le';
    public const UTF32 = 'utf32';
    public const UTF8 = 'utf8';
    public const UTF8MB3 = 'utf8mb3';
    public const UTF8MB4 = 'utf8mb4';

    /** @var array<string, int> */
    private static array $ids = [
        self::BIG5 => 1,
        self::DEC8 => 3,
        self::CP850 => 4,
        self::HP8 => 6,
        self::KOI8R => 7,
        self::LATIN1 => 8,
        self::LATIN2 => 9,
        self::SWE7 => 10,
        self::ASCII => 11,
        self::UJIS => 12,
        self::SJIS => 13,
        self::HEBREW => 16,
        self::TIS620 => 18,
        self::EUCKR => 19,
        self::KOI8U => 22,
        self::GB2312 => 24,
        self::GREEK => 25,
        self::CP1250 => 26,
        self::GBK => 28,
        self::LATIN5 => 30,
        self::ARMSCII8 => 32,
        self::UTF8 => 33,
        self::UTF8MB3 => 33, // ???
        self::UCS2 => 35,
        self::CP866 => 36,
        self::KEYBCS2 => 37,
        self::MACCE => 38,
        self::MACROMAN => 39,
        self::MACLATIN2 => 39,
        self::CP852 => 40,
        self::LATIN7 => 41,
        self::CP1251 => 51,
        self::UTF16 => 54,
        self::UTF16LE => 56,
        self::CP1256 => 57,
        self::CP1257 => 59,
        self::UTF32 => 60,
        self::BINARY => 63,
        self::GEOSTD8 => 92,
        self::CP932 => 95,
        self::EUCJPMS => 97,
        self::GB18030 => 248,
        self::UTF8MB4 => 255,
    ];

    /** @var array<string, string> */
    private static array $defaultCollations = [
        self::ARMSCII8 => Collation::ARMSCII8_GENERAL_CI,
        self::ASCII => Collation::ASCII_GENERAL_CI,
        self::BIG5 => Collation::BIG5_CHINESE_CI,
        self::BINARY => Collation::BINARY,
        self::CP1250 => Collation::CP1250_GENERAL_CI,
        self::CP1251 => Collation::CP1251_GENERAL_CI,
        self::CP1256 => Collation::CP1256_GENERAL_CI,
        self::CP1257 => Collation::CP1257_GENERAL_CI,
        self::CP850 => Collation::CP850_GENERAL_CI,
        self::CP852 => Collation::CP852_GENERAL_CI,
        self::CP866 => Collation::CP866_GENERAL_CI,
        self::CP932 => Collation::CP932_JAPANESE_CI,
        self::DEC8 => Collation::DEC8_SWEDISH_CI,
        self::EUCJPMS => Collation::EUCJPMS_JAPANESE_CI,
        self::EUCKR => Collation::EUCKR_KOREAN_CI,
        self::GB18030 => Collation::GB18030_CHINESE_CI,
        self::GB2312 => Collation::GB2312_CHINESE_CI,
        self::GBK => Collation::GBK_CHINESE_CI,
        self::GEOSTD8 => Collation::GEOSTD8_GENERAL_CI,
        self::GREEK => Collation::GREEK_GENERAL_CI,
        self::HEBREW => Collation::HEBREW_GENERAL_CI,
        self::HP8 => Collation::HP8_ENGLISH_CI,
        self::KEYBCS2 => Collation::KEYBCS2_GENERAL_CI,
        self::KOI8R => Collation::KOI8R_GENERAL_CI,
        self::KOI8U => Collation::KOI8U_GENERAL_CI,
        self::LATIN1 => Collation::LATIN1_SWEDISH_CI,
        self::LATIN2 => Collation::LATIN2_GENERAL_CI,
        self::LATIN5 => Collation::LATIN5_TURKISH_CI,
        self::LATIN7 => Collation::LATIN7_GENERAL_CI,
        self::MACCE => Collation::MACCE_GENERAL_CI,
        self::MACROMAN => Collation::MACROMAN_GENERAL_CI,
        self::SJIS => Collation::SJIS_JAPANESE_CI,
        self::SWE7 => Collation::SWE7_SWEDISH_CI,
        self::TIS620 => Collation::TIS620_THAI_CI,
        self::UCS2 => Collation::UCS2_GENERAL_CI,
        self::UJIS => Collation::UJIS_JAPANESE_CI,
        self::UTF16 => Collation::UTF16_GENERAL_CI,
        self::UTF16LE => Collation::UTF16LE_GENERAL_CI,
        self::UTF32 => Collation::UTF32_GENERAL_CI,
        self::UTF8 => Collation::UTF8_GENERAL_CI,
        self::UTF8MB4 => Collation::UTF8MB4_GENERAL_0900_AI_CI,
    ];

    protected static function validateValue(string &$value): bool
    {
        $value = strtolower($value);

        if ($value === 'mac_latin2') {
            return parent::validateValue($value);
        } elseif (strpos($value, '_') !== false) {
            // things like 'cp1250_latin2' are valid
            // todo: ignoring the second part
            [$value, $value2] = explode('_', $value);
            // some compatibility shit. koi8 is not valid by itself
            if ($value2 === 'koi8') {
                $value2 = 'koi8r';
            }

            return parent::validateValue($value) && parent::validateValue($value2);
        } else {
            return parent::validateValue($value);
        }
    }

    public function getId(): int
    {
        return self::$ids[$this->getValue()];
    }

    public static function getById(int $id): self
    {
        $key = array_search($id, self::$ids, true);
        if ($key === false) {
            throw new InvalidDefinitionException("Unknown charset id: $id");
        }

        return new self($key);
    }

    public function getDefaultCollationName(): string
    {
        return self::$defaultCollations[$this->getValue()];
    }

    public function supportsCollation(Collation $collation): bool
    {
        if ($collation->equalsValue(Collation::BINARY)) {
            return true;
        } else {
            return $this->equalsValue($collation->getCharsetName());
        }
    }

}
