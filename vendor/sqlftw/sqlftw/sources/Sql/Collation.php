<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql;

use Dogma\Str;
use function array_search;

class Collation extends SqlEnum
{

    public const ARMSCII8_BIN = 'armscii8_bin';
    public const ARMSCII8_GENERAL_CI = 'armscii8_general_ci';

    public const ASCII_BIN = 'ascii_bin';
    public const ASCII_GENERAL_CI = 'ascii_general_ci';

    public const BIG5_BIN = 'big5_bin';
    public const BIG5_CHINESE_CI = 'big5_chinese_ci';

    public const BINARY = 'binary';

    public const CP1250_BIN = 'cp1250_bin';
    public const CP1250_CROATIAN_CI = 'cp1250_croatian_ci';
    public const CP1250_CZECH_CS = 'cp1250_czech_cs';
    public const CP1250_GENERAL_CI = 'cp1250_general_ci';
    public const CP1250_POLISH_CI = 'cp1250_polish_ci';

    public const CP1251_BIN = 'cp1251_bin';
    public const CP1251_BULGARIAN_CI = 'cp1251_bulgarian_ci';
    public const CP1251_GENERAL_CI = 'cp1251_general_ci';
    public const CP1251_GENERAL_CS = 'cp1251_general_cs';
    public const CP1251_UKRAINIAN_CI = 'cp1251_ukrainian_ci';

    public const CP1256_BIN = 'cp1256_bin';
    public const CP1256_GENERAL_CI = 'cp1256_general_ci';

    public const CP1257_BIN = 'cp1257_bin';
    public const CP1257_GENERAL_CI = 'cp1257_general_ci';
    public const CP1257_LITHUANIAN_CI = 'cp1257_lithuanian_ci';

    public const CP850_BIN = 'cp850_bin';
    public const CP850_GENERAL_CI = 'cp850_general_ci';

    public const CP852_BIN = 'cp852_bin';
    public const CP852_GENERAL_CI = 'cp852_general_ci';

    public const CP866_BIN = 'cp866_bin';
    public const CP866_GENERAL_CI = 'cp866_general_ci';

    public const CP932_BIN = 'cp932_bin';
    public const CP932_JAPANESE_CI = 'cp932_japanese_ci';

    public const DEC8_BIN = 'dec8_bin';
    public const DEC8_SWEDISH_CI = 'dec8_swedish_ci';

    public const EUCJPMS_BIN = 'eucjpms_bin';
    public const EUCJPMS_JAPANESE_CI = 'eucjpms_japanese_ci';

    public const EUCKR_BIN = 'euckr_bin';
    public const EUCKR_KOREAN_CI = 'euckr_korean_ci';

    public const GB18030_BIN = 'gb18030_bin';
    public const GB18030_CHINESE_CI = 'gb18030_chinese_ci';
    public const GB18030_UNICODE_520_CI = 'gb18030_unicode_520_ci';

    public const GB2312_BIN = 'gb2312_bin';
    public const GB2312_CHINESE_CI = 'gb2312_chinese_ci';

    public const GBK_BIN = 'gbk_bin';
    public const GBK_CHINESE_CI = 'gbk_chinese_ci';

    public const GEOSTD8_BIN = 'geostd8_bin';
    public const GEOSTD8_GENERAL_CI = 'geostd8_general_ci';

    public const GREEK_BIN = 'greek_bin';
    public const GREEK_GENERAL_CI = 'greek_general_ci';

    public const HEBREW_BIN = 'hebrew_bin';
    public const HEBREW_GENERAL_CI = 'hebrew_general_ci';

    public const HP8_BIN = 'hp8_bin';
    public const HP8_ENGLISH_CI = 'hp8_english_ci';

    public const KEYBCS2_BIN = 'keybcs2_bin';
    public const KEYBCS2_GENERAL_CI = 'keybcs2_general_ci';

    public const KOI8R_BIN = 'koi8r_bin';
    public const KOI8R_GENERAL_CI = 'koi8r_general_ci';

    public const KOI8U_BIN = 'koi8u_bin';
    public const KOI8U_GENERAL_CI = 'koi8u_general_ci';

    public const LATIN1_BIN = 'latin1_bin';
    public const LATIN1_DANISH_CI = 'latin1_danish_ci';
    public const LATIN1_GENERAL_CI = 'latin1_general_ci';
    public const LATIN1_GENERAL_CS = 'latin1_general_cs';
    public const LATIN1_GERMAN1_CI = 'latin1_german1_ci';
    public const LATIN1_GERMAN2_CI = 'latin1_german2_ci';
    public const LATIN1_SPANISH_CI = 'latin1_spanish_ci';
    public const LATIN1_SWEDISH_CI = 'latin1_swedish_ci';

    public const LATIN2_BIN = 'latin2_bin';
    public const LATIN2_CROATIAN_CI = 'latin2_croatian_ci';
    public const LATIN2_CZECH_CS = 'latin2_czech_cs';
    public const LATIN2_GENERAL_CI = 'latin2_general_ci';
    public const LATIN2_HUNGARIAN_CI = 'latin2_hungarian_ci';

    public const LATIN5_BIN = 'latin5_bin';
    public const LATIN5_TURKISH_CI = 'latin5_turkish_ci';

    public const LATIN7_BIN = 'latin7_bin';
    public const LATIN7_ESTONIAN_CS = 'latin7_estonian_cs';
    public const LATIN7_GENERAL_CI = 'latin7_general_ci';
    public const LATIN7_GENERAL_CS = 'latin7_general_cs';

    public const MACCE_BIN = 'macce_bin';
    public const MACCE_GENERAL_CI = 'macce_general_ci';

    public const MACROMAN_BIN = 'macroman_bin';
    public const MACROMAN_GENERAL_CI = 'macroman_general_ci';

    public const SJIS_BIN = 'sjis_bin';
    public const SJIS_JAPANESE_CI = 'sjis_japanese_ci';

    public const SWE7_BIN = 'swe7_bin';
    public const SWE7_SWEDISH_CI = 'swe7_swedish_ci';

    public const TIS620_BIN = 'tis620_bin';
    public const TIS620_THAI_CI = 'tis620_thai_ci';

    public const UCS2_BIN = 'ucs2_bin';
    public const UCS2_CROATIAN_CI = 'ucs2_croatian_ci';
    public const UCS2_CZECH_CI = 'ucs2_czech_ci';
    public const UCS2_DANISH_CI = 'ucs2_danish_ci';
    public const UCS2_ESPERANTO_CI = 'ucs2_esperanto_ci';
    public const UCS2_ESTONIAN_CI = 'ucs2_estonian_ci';
    public const UCS2_GENERAL_CI = 'ucs2_general_ci';
    public const UCS2_GENERAL_MYSQL500_CI = 'ucs2_general_mysql500_ci';
    public const UCS2_GERMAN2_CI = 'ucs2_german2_ci';
    public const UCS2_HUNGARIAN_CI = 'ucs2_hungarian_ci';
    public const UCS2_ICELANDIC_CI = 'ucs2_icelandic_ci';
    public const UCS2_LATVIAN_CI = 'ucs2_latvian_ci';
    public const UCS2_LITHUANIAN_CI = 'ucs2_lithuanian_ci';
    public const UCS2_PERSIAN_CI = 'ucs2_persian_ci';
    public const UCS2_POLISH_CI = 'ucs2_polish_ci';
    public const UCS2_ROMAN_CI = 'ucs2_roman_ci';
    public const UCS2_ROMANIAN_CI = 'ucs2_romanian_ci';
    public const UCS2_SINHALA_CI = 'ucs2_sinhala_ci';
    public const UCS2_SLOVAK_CI = 'ucs2_slovak_ci';
    public const UCS2_SLOVENIAN_CI = 'ucs2_slovenian_ci';
    public const UCS2_SPANISH2_CI = 'ucs2_spanish2_ci';
    public const UCS2_SPANISH_CI = 'ucs2_spanish_ci';
    public const UCS2_SWEDISH_CI = 'ucs2_swedish_ci';
    public const UCS2_TURKISH_CI = 'ucs2_turkish_ci';
    public const UCS2_UNICODE_520_CI = 'ucs2_unicode_520_ci';
    public const UCS2_UNICODE_CI = 'ucs2_unicode_ci';
    public const UCS2_VIETNAMESE_CI = 'ucs2_vietnamese_ci';

    public const UCS2_VN_CI = 'ucs2_vn_ci'; // todo: are all names short in 8.0?

    public const UJIS_BIN = 'ujis_bin';
    public const UJIS_JAPANESE_CI = 'ujis_japanese_ci';

    public const UTF16_BIN = 'utf16_bin';
    public const UTF16_CROATIAN_CI = 'utf16_croatian_ci';
    public const UTF16_CZECH_CI = 'utf16_czech_ci';
    public const UTF16_DANISH_CI = 'utf16_danish_ci';
    public const UTF16_ESPERANTO_CI = 'utf16_esperanto_ci';
    public const UTF16_ESTONIAN_CI = 'utf16_estonian_ci';
    public const UTF16_GENERAL_CI = 'utf16_general_ci';
    public const UTF16_GERMAN2_CI = 'utf16_german2_ci';
    public const UTF16_HUNGARIAN_CI = 'utf16_hungarian_ci';
    public const UTF16_ICELANDIC_CI = 'utf16_icelandic_ci';
    public const UTF16_LATVIAN_CI = 'utf16_latvian_ci';
    public const UTF16_LITHUANIAN_CI = 'utf16_lithuanian_ci';
    public const UTF16_PERSIAN_CI = 'utf16_persian_ci';
    public const UTF16_POLISH_CI = 'utf16_polish_ci';
    public const UTF16_ROMAN_CI = 'utf16_roman_ci';
    public const UTF16_ROMANIAN_CI = 'utf16_romanian_ci';
    public const UTF16_SINHALA_CI = 'utf16_sinhala_ci';
    public const UTF16_SLOVAK_CI = 'utf16_slovak_ci';
    public const UTF16_SLOVENIAN_CI = 'utf16_slovenian_ci';
    public const UTF16_SPANISH2_CI = 'utf16_spanish2_ci';
    public const UTF16_SPANISH_CI = 'utf16_spanish_ci';
    public const UTF16_SWEDISH_CI = 'utf16_swedish_ci';
    public const UTF16_TURKISH_CI = 'utf16_turkish_ci';
    public const UTF16_UNICODE_520_CI = 'utf16_unicode_520_ci';
    public const UTF16_UNICODE_CI = 'utf16_unicode_ci';
    public const UTF16_VIETNAMESE_CI = 'utf16_vietnamese_ci';

    public const UTF16LE_BIN = 'utf16le_bin';
    public const UTF16LE_GENERAL_CI = 'utf16le_general_ci';

    public const UTF32_BIN = 'utf32_bin';
    public const UTF32_CROATIAN_CI = 'utf32_croatian_ci';
    public const UTF32_CZECH_CI = 'utf32_czech_ci';
    public const UTF32_DANISH_CI = 'utf32_danish_ci';
    public const UTF32_ESPERANTO_CI = 'utf32_esperanto_ci';
    public const UTF32_ESTONIAN_CI = 'utf32_estonian_ci';
    public const UTF32_GENERAL_CI = 'utf32_general_ci';
    public const UTF32_GERMAN2_CI = 'utf32_german2_ci';
    public const UTF32_HUNGARIAN_CI = 'utf32_hungarian_ci';
    public const UTF32_ICELANDIC_CI = 'utf32_icelandic_ci';
    public const UTF32_LATVIAN_CI = 'utf32_latvian_ci';
    public const UTF32_LITHUANIAN_CI = 'utf32_lithuanian_ci';
    public const UTF32_PERSIAN_CI = 'utf32_persian_ci';
    public const UTF32_POLISH_CI = 'utf32_polish_ci';
    public const UTF32_ROMAN_CI = 'utf32_roman_ci';
    public const UTF32_ROMANIAN_CI = 'utf32_romanian_ci';
    public const UTF32_SINHALA_CI = 'utf32_sinhala_ci';
    public const UTF32_SLOVAK_CI = 'utf32_slovak_ci';
    public const UTF32_SLOVENIAN_CI = 'utf32_slovenian_ci';
    public const UTF32_SPANISH2_CI = 'utf32_spanish2_ci';
    public const UTF32_SPANISH_CI = 'utf32_spanish_ci';
    public const UTF32_SWEDISH_CI = 'utf32_swedish_ci';
    public const UTF32_TURKISH_CI = 'utf32_turkish_ci';
    public const UTF32_UNICODE_520_CI = 'utf32_unicode_520_ci';
    public const UTF32_UNICODE_CI = 'utf32_unicode_ci';
    public const UTF32_VIETNAMESE_CI = 'utf32_vietnamese_ci';

    public const UTF8_BIN = 'utf8_bin';
    public const UTF8_CROATIAN_CI = 'utf8_croatian_ci';
    public const UTF8_CZECH_CI = 'utf8_czech_ci';
    public const UTF8_DANISH_CI = 'utf8_danish_ci';
    public const UTF8_ESPERANTO_CI = 'utf8_esperanto_ci';
    public const UTF8_ESTONIAN_CI = 'utf8_estonian_ci';
    public const UTF8_GENERAL_CI = 'utf8_general_ci';
    public const UTF8_GENERAL_MYSQL500_CI = 'utf8_general_mysql500_ci';
    public const UTF8_GERMAN2_CI = 'utf8_german2_ci';
    public const UTF8_HUNGARIAN_CI = 'utf8_hungarian_ci';
    public const UTF8_ICELANDIC_CI = 'utf8_icelandic_ci';
    public const UTF8_LATVIAN_CI = 'utf8_latvian_ci';
    public const UTF8_LITHUANIAN_CI = 'utf8_lithuanian_ci';
    public const UTF8_PERSIAN_CI = 'utf8_persian_ci';
    public const UTF8_POLISH_CI = 'utf8_polish_ci';
    public const UTF8_ROMAN_CI = 'utf8_roman_ci';
    public const UTF8_ROMANIAN_CI = 'utf8_romanian_ci';
    public const UTF8_SINHALA_CI = 'utf8_sinhala_ci';
    public const UTF8_SLOVAK_CI = 'utf8_slovak_ci';
    public const UTF8_SLOVENIAN_CI = 'utf8_slovenian_ci';
    public const UTF8_SPANISH2_CI = 'utf8_spanish2_ci';
    public const UTF8_SPANISH_CI = 'utf8_spanish_ci';
    public const UTF8_SWEDISH_CI = 'utf8_swedish_ci';
    public const UTF8_TURKISH_CI = 'utf8_turkish_ci';
    public const UTF8_UNICODE_520_CI = 'utf8_unicode_520_ci';
    public const UTF8_UNICODE_CI = 'utf8_unicode_ci';
    public const UTF8_VIETNAMESE_CI = 'utf8_vietnamese_ci';

    public const UTF8MB3_BIN = 'utf8mb3_bin';
    public const UTF8MB3_CROATIAN_CI = 'utf8mb3_croatian_ci';
    public const UTF8MB3_CZECH_CI = 'utf8mb3_czech_ci';
    public const UTF8MB3_DANISH_CI = 'utf8mb3_danish_ci';
    public const UTF8MB3_ESPERANTO_CI = 'utf8mb3_esperanto_ci';
    public const UTF8MB3_ESTONIAN_CI = 'utf8mb3_estonian_ci';
    public const UTF8MB3_GENERAL_CI = 'utf8mb3_general_ci';
    public const UTF8MB3_GENERAL_MYSQL500_CI = 'utf8mb3_general_mysql500_ci';
    public const UTF8MB3_GERMAN2_CI = 'utf8mb3_german2_ci';
    public const UTF8MB3_HUNGARIAN_CI = 'utf8mb3_hungarian_ci';
    public const UTF8MB3_ICELANDIC_CI = 'utf8mb3_icelandic_ci';
    public const UTF8MB3_LATVIAN_CI = 'utf8mb3_latvian_ci';
    public const UTF8MB3_LITHUANIAN_CI = 'utf8mb3_lithuanian_ci';
    public const UTF8MB3_PERSIAN_CI = 'utf8mb3_persian_ci';
    public const UTF8MB3_POLISH_CI = 'utf8mb3_polish_ci';
    public const UTF8MB3_ROMAN_CI = 'utf8mb3_roman_ci';
    public const UTF8MB3_ROMANIAN_CI = 'utf8mb3_romanian_ci';
    public const UTF8MB3_SINHALA_CI = 'utf8mb3_sinhala_ci';
    public const UTF8MB3_SLOVAK_CI = 'utf8mb3_slovak_ci';
    public const UTF8MB3_SLOVENIAN_CI = 'utf8mb3_slovenian_ci';
    public const UTF8MB3_SPANISH2_CI = 'utf8mb3_spanish2_ci';
    public const UTF8MB3_SPANISH_CI = 'utf8mb3_spanish_ci';
    public const UTF8MB3_SWEDISH_CI = 'utf8mb3_swedish_ci';
    public const UTF8MB3_TURKISH_CI = 'utf8mb3_turkish_ci';
    public const UTF8MB3_UNICODE_520_CI = 'utf8mb3_unicode_520_ci';
    public const UTF8MB3_UNICODE_CI = 'utf8mb3_unicode_ci';
    public const UTF8MB3_VIETNAMESE_CI = 'utf8mb3_vietnamese_ci';

    public const UTF8MB4_BIN = 'utf8mb4_bin';
    public const UTF8MB4_CROATIAN_CI = 'utf8mb4_croatian_ci';
    public const UTF8MB4_CZECH_CI = 'utf8mb4_czech_ci';
    public const UTF8MB4_DANISH_CI = 'utf8mb4_danish_ci';
    public const UTF8MB4_ESPERANTO_CI = 'utf8mb4_esperanto_ci';
    public const UTF8MB4_ESTONIAN_CI = 'utf8mb4_estonian_ci';
    public const UTF8MB4_GENERAL_CI = 'utf8mb4_general_ci';
    public const UTF8MB4_GERMAN2_CI = 'utf8mb4_german2_ci';
    public const UTF8MB4_HUNGARIAN_CI = 'utf8mb4_hungarian_ci';
    public const UTF8MB4_ICELANDIC_CI = 'utf8mb4_icelandic_ci';
    public const UTF8MB4_LATVIAN_CI = 'utf8mb4_latvian_ci';
    public const UTF8MB4_LITHUANIAN_CI = 'utf8mb4_lithuanian_ci';
    public const UTF8MB4_PERSIAN_CI = 'utf8mb4_persian_ci';
    public const UTF8MB4_POLISH_CI = 'utf8mb4_polish_ci';
    public const UTF8MB4_ROMAN_CI = 'utf8mb4_roman_ci';
    public const UTF8MB4_ROMANIAN_CI = 'utf8mb4_romanian_ci';
    public const UTF8MB4_SINHALA_CI = 'utf8mb4_sinhala_ci';
    public const UTF8MB4_SLOVAK_CI = 'utf8mb4_slovak_ci';
    public const UTF8MB4_SLOVENIAN_CI = 'utf8mb4_slovenian_ci';
    public const UTF8MB4_SPANISH2_CI = 'utf8mb4_spanish2_ci';
    public const UTF8MB4_SPANISH_CI = 'utf8mb4_spanish_ci';
    public const UTF8MB4_SWEDISH_CI = 'utf8mb4_swedish_ci';
    public const UTF8MB4_TURKISH_CI = 'utf8mb4_turkish_ci';
    public const UTF8MB4_UNICODE_520_CI = 'utf8mb4_unicode_520_ci';
    public const UTF8MB4_UNICODE_CI = 'utf8mb4_unicode_ci';
    public const UTF8MB4_VIETNAMESE_CI = 'utf8mb4_vietnamese_ci';

    // MySQL 8.0
    public const UTF8MB4_BOSNIAN_0900_AI_CI = 'utf8mb4_bs_0900_ai_ci';
    public const UTF8MB4_BOSNIAN_0900_AS_CS = 'utf8mb4_bs_0900_as_cs';
    public const UTF8MB4_BULGARIAN_0900_AI_CI = 'utf8mb4_bg_0900_ai_ci';
    public const UTF8MB4_BULGARIAN_0900_AS_CS = 'utf8mb4_bg_0900_as_cs';
    public const UTF8MB4_CHINESE_0900_AS_CS = 'utf8mb4_zh_0900_as_cs';
    public const UTF8MB4_CLASSICAL_LATIN_0900_AI_CI = 'utf8mb4_la_0900_ai_ci';
    public const UTF8MB4_CLASSICAL_LATIN_0900_AS_CS = 'utf8mb4_la_0900_as_cs';
    public const UTF8MB4_CROATIAN_0900_AI_CI = 'utf8mb4_hr_0900_ai_ci';
    public const UTF8MB4_CROATIAN_0900_AS_CS = 'utf8mb4_hr_0900_as_cs';
    public const UTF8MB4_CZECH_0900_AI_CI = 'utf8mb4_cs_0900_ai_ci';
    public const UTF8MB4_CZECH_0900_AS_CS = 'utf8mb4_cs_0900_as_cs';
    public const UTF8MB4_DANISH_0900_AI_CI = 'utf8mb4_da_0900_ai_ci'; // also good for: no, nb, nn
    public const UTF8MB4_DANISH_0900_AS_CS = 'utf8mb4_da_0900_as_cs'; // also good for: no, nb, nn
    public const UTF8MB4_ESPERANTO_0900_AI_CI = 'utf8mb4_eo_0900_ai_ci';
    public const UTF8MB4_ESPERANTO_0900_AS_CS = 'utf8mb4_eo_0900_as_cs';
    public const UTF8MB4_ESTONIAN_0900_AI_CI = 'utf8mb4_et_0900_ai_ci';
    public const UTF8MB4_ESTONIAN_0900_AS_CS = 'utf8mb4_et_0900_as_cs';
    public const UTF8MB4_GALICIAN_0900_AI_CI = 'utf8mb4_gl_0900_ai_ci';
    public const UTF8MB4_GALICIAN_0900_AS_CS = 'utf8mb4_gl_0900_as_cs';
    public const UTF8MB4_GENERAL_0900_AI_CI = 'utf8mb4_0900_ai_ci'; // good for: de, en, fr, ga, id, it, lb, ms, nl, pt, sw, zu
    public const UTF8MB4_GENERAL_0900_AS_CS = 'utf8mb4_0900_as_cs'; // good for: de, en, fr, ga, id, it, lb, ms, nl, pt, sw, zu
    public const UTF8MB4_GERMAN_PHONE_BOOK_0900_AI_CI = 'utf8mb4_de_pb_0900_ai_ci';
    public const UTF8MB4_GERMAN_PHONE_BOOK_0900_AS_CS = 'utf8mb4_de_pb_0900_as_cs';
    public const UTF8MB4_HUNGARIAN_0900_AI_CI = 'utf8mb4_hu_0900_ai_ci';
    public const UTF8MB4_HUNGARIAN_0900_AS_CS = 'utf8mb4_hu_0900_as_cs';
    public const UTF8MB4_ICELANDIC_0900_AI_CI = 'utf8mb4_is_0900_ai_ci';
    public const UTF8MB4_ICELANDIC_0900_AS_CS = 'utf8mb4_is_0900_as_cs';
    public const UTF8MB4_JAPANESE_0900_AI_CI = 'utf8mb4_ja_0900_ai_ci'; // todo: id
    public const UTF8MB4_JAPANESE_0900_AS_CS = 'utf8mb4_ja_0900_as_cs';
    public const UTF8MB4_JAPANESE_0900_AS_CS_KS = 'utf8mb4_ja_0900_as_cs_ks'; // Katakana vs Hiragana
    public const UTF8MB4_LATVIAN_0900_AI_CI = 'utf8mb4_lv_0900_ai_ci';
    public const UTF8MB4_LATVIAN_0900_AS_CS = 'utf8mb4_lv_0900_as_cs';
    public const UTF8MB4_LITHUANIAN_0900_AI_CI = 'utf8mb4_lt_0900_ai_ci';
    public const UTF8MB4_LITHUANIAN_0900_AS_CS = 'utf8mb4_lt_0900_as_cs';
    public const UTF8MB4_MONGOLIAN_CYRILLIC_0900_AI_CI = 'utf8mb4_mn_cyrl_0900_ai_ci';
    public const UTF8MB4_MONGOLIAN_CYRILLIC_0900_AS_CS = 'utf8mb4_mn_cyrl_0900_as_cs';
    public const UTF8MB4_NORWEGIAN_0900_AI_CI = 'utf8mb4_no_0900_ai_ci'; // todo: id
    public const UTF8MB4_NORWEGIAN_BOKMAL_0900_AI_CI = 'utf8mb4_nb_0900_ai_ci';
    public const UTF8MB4_NORWEGIAN_BOKMAL_0900_AS_CS = 'utf8mb4_nb_0900_as_cs';
    public const UTF8MB4_NORWEGIAN_NYNORSK_0900_AI_CI = 'utf8mb4_nn_0900_ai_ci';
    public const UTF8MB4_NORWEGIAN_NYNORSK_0900_AS_CS = 'utf8mb4_nn_0900_as_cs';
    public const UTF8MB4_POLISH_0900_AI_CI = 'utf8mb4_pl_0900_ai_ci';
    public const UTF8MB4_POLISH_0900_AS_CS = 'utf8mb4_pl_0900_as_cs';
    public const UTF8MB4_ROMANIAN_0900_AI_CI = 'utf8mb4_ro_0900_ai_ci';
    public const UTF8MB4_ROMANIAN_0900_AS_CS = 'utf8mb4_ro_0900_as_cs';
    public const UTF8MB4_RUSSIAN_0900_AI_CI = 'utf8mb4_ru_0900_ai_ci';
    public const UTF8MB4_RUSSIAN_0900_AS_CS = 'utf8mb4_ru_0900_as_cs';
    public const UTF8MB4_SERBIAN_LATIN_0900_AI_CI = 'utf8mb4_sr_latn_0900_ai_ci';
    public const UTF8MB4_SERBIAN_LATIN_0900_AS_CS = 'utf8mb4_sr_latn_0900_as_cs';
    public const UTF8MB4_SLOVAK_0900_AI_CI = 'utf8mb4_sk_0900_ai_ci';
    public const UTF8MB4_SLOVAK_0900_AS_CS = 'utf8mb4_sk_0900_as_cs';
    public const UTF8MB4_SLOVENIAN_0900_AI_CI = 'utf8mb4_sl_0900_ai_ci';
    public const UTF8MB4_SLOVENIAN_0900_AS_CS = 'utf8mb4_sl_0900_as_cs';
    public const UTF8MB4_SPANISH_0900_AI_CI = 'utf8mb4_es_0900_ai_ci';
    public const UTF8MB4_SPANISH_0900_AS_CS = 'utf8mb4_es_0900_as_cs';
    public const UTF8MB4_SWEDISH_0900_AI_CI = 'utf8mb4_sv_0900_ai_ci';
    public const UTF8MB4_SWEDISH_0900_AS_CS = 'utf8mb4_sv_0900_as_cs';
    public const UTF8MB4_TRADITIONAL_SPANISH_0900_AI_CI = 'utf8mb4_es_trad_0900_ai_ci';
    public const UTF8MB4_TRADITIONAL_SPANISH_0900_AS_CS = 'utf8mb4_es_trad_0900_as_cs';
    public const UTF8MB4_TURKISH_0900_AI_CI = 'utf8mb4_tr_0900_ai_ci';
    public const UTF8MB4_TURKISH_0900_AS_CS = 'utf8mb4_tr_0900_as_cs';
    public const UTF8MB4_VIETNAMESE_0900_AI_CI = 'utf8mb4_vi_0900_ai_ci';
    public const UTF8MB4_VIETNAMESE_0900_AS_CS = 'utf8mb4_vi_0900_as_cs';

    public const UTF8MB4_GENERAL_0900_AS_CI = 'utf8mb4_0900_as_ci';
    public const UTF8MB4_GENERAL_0900_BIN = 'utf8mb4_0900_bin';

    // from tests. ids not known
    public const UTF8_PHONE_CI = 'utf8_phone_ci';
    public const UTF8_TOLOWER_CI = 'utf8_tolower_ci';
    public const UTF8_BENGALI_STANDARD_CI = 'utf8_bengali_standard_ci';
    public const UTF8_BENGALI_TRADITIONAL_CI = 'utf8_bengali_traditional_ci';

    /** @internal */
    public const UTF8_MAXUSERID_CI = 'utf8_maxuserid_ci';
    /** @internal */
    public const UTF8_TEST_CI = 'utf8_test_ci';
    /** @internal */
    public const UCS2_TEST_CI = 'ucs2_test_ci';
    /** @internal */
    public const UTF8MB4_TEST_CI = 'utf8mb4_test_ci';
    /** @internal */
    public const UTF8MB4_TEST_400_CI = 'utf8mb4_test_400_ci';
    /** @internal */
    public const UTF16_TEST_CI = 'utf16_test_ci';
    /** @internal */
    public const UTF32_TEST_CI = 'utf32_test_ci';
    /** @internal */
    public const LATIN1_TEST = 'latin1_test';

    public const UCS2_5624_1 = 'ucs2_5624_1';
    public const UTF8_5624_1 = 'utf8_5624_1';
    public const UTF8_5624_4 = 'utf8_5624_4';
    public const UTF8_5624_5 = 'utf8_5624_5';

    /** @var array<string, int> */
    private static array $ids = [
        self::ARMSCII8_BIN => 64,
        self::ARMSCII8_GENERAL_CI => 32,
        self::ASCII_BIN => 65,
        self::ASCII_GENERAL_CI => 11,
        self::BIG5_BIN => 84,
        self::BIG5_CHINESE_CI => 1,
        self::BINARY => 63,
        self::CP1250_BIN => 66,
        self::CP1250_CROATIAN_CI => 44,
        self::CP1250_CZECH_CS => 34,
        self::CP1250_GENERAL_CI => 26,
        self::CP1250_POLISH_CI => 99,
        self::CP1251_BIN => 50,
        self::CP1251_BULGARIAN_CI => 14,
        self::CP1251_GENERAL_CI => 51,
        self::CP1251_GENERAL_CS => 52,
        self::CP1251_UKRAINIAN_CI => 23,
        self::CP1256_BIN => 67,
        self::CP1256_GENERAL_CI => 57,
        self::CP1257_BIN => 58,
        self::CP1257_GENERAL_CI => 59,
        self::CP1257_LITHUANIAN_CI => 29,
        self::CP850_BIN => 80,
        self::CP850_GENERAL_CI => 4,
        self::CP852_BIN => 81,
        self::CP852_GENERAL_CI => 40,
        self::CP866_BIN => 68,
        self::CP866_GENERAL_CI => 36,
        self::CP932_BIN => 96,
        self::CP932_JAPANESE_CI => 95,
        self::DEC8_BIN => 69,
        self::DEC8_SWEDISH_CI => 3,
        self::EUCJPMS_BIN => 98,
        self::EUCJPMS_JAPANESE_CI => 97,
        self::EUCKR_BIN => 85,
        self::EUCKR_KOREAN_CI => 19,
        self::GB18030_BIN => 249,
        self::GB18030_CHINESE_CI => 248,
        self::GB18030_UNICODE_520_CI => 250,
        self::GB2312_BIN => 86,
        self::GB2312_CHINESE_CI => 24,
        self::GBK_BIN => 87,
        self::GBK_CHINESE_CI => 28,
        self::GEOSTD8_BIN => 93,
        self::GEOSTD8_GENERAL_CI => 92,
        self::GREEK_BIN => 70,
        self::GREEK_GENERAL_CI => 25,
        self::HEBREW_BIN => 71,
        self::HEBREW_GENERAL_CI => 16,
        self::HP8_BIN => 72,
        self::HP8_ENGLISH_CI => 6,
        self::KEYBCS2_BIN => 73,
        self::KEYBCS2_GENERAL_CI => 37,
        self::KOI8R_BIN => 74,
        self::KOI8R_GENERAL_CI => 7,
        self::KOI8U_BIN => 75,
        self::KOI8U_GENERAL_CI => 22,
        self::LATIN1_BIN => 47,
        self::LATIN1_DANISH_CI => 15,
        self::LATIN1_GENERAL_CI => 48,
        self::LATIN1_GENERAL_CS => 49,
        self::LATIN1_GERMAN1_CI => 5,
        self::LATIN1_GERMAN2_CI => 31,
        self::LATIN1_SPANISH_CI => 94,
        self::LATIN1_SWEDISH_CI => 8,
        self::LATIN2_BIN => 77,
        self::LATIN2_CROATIAN_CI => 27,
        self::LATIN2_CZECH_CS => 2,
        self::LATIN2_GENERAL_CI => 9,
        self::LATIN2_HUNGARIAN_CI => 21,
        self::LATIN5_BIN => 78,
        self::LATIN5_TURKISH_CI => 30,
        self::LATIN7_BIN => 79,
        self::LATIN7_ESTONIAN_CS => 20,
        self::LATIN7_GENERAL_CI => 41,
        self::LATIN7_GENERAL_CS => 42,
        self::MACCE_BIN => 43,
        self::MACCE_GENERAL_CI => 38,
        self::MACROMAN_BIN => 53,
        self::MACROMAN_GENERAL_CI => 39,
        self::SJIS_BIN => 88,
        self::SJIS_JAPANESE_CI => 13,
        self::SWE7_BIN => 82,
        self::SWE7_SWEDISH_CI => 10,
        self::TIS620_BIN => 89,
        self::TIS620_THAI_CI => 18,
        self::UCS2_BIN => 90,
        self::UCS2_CROATIAN_CI => 149,
        self::UCS2_CZECH_CI => 138,
        self::UCS2_DANISH_CI => 139,
        self::UCS2_ESPERANTO_CI => 145,
        self::UCS2_ESTONIAN_CI => 134,
        self::UCS2_GENERAL_CI => 35,
        self::UCS2_GENERAL_MYSQL500_CI => 159,
        self::UCS2_GERMAN2_CI => 148,
        self::UCS2_HUNGARIAN_CI => 146,
        self::UCS2_ICELANDIC_CI => 129,
        self::UCS2_LATVIAN_CI => 130,
        self::UCS2_LITHUANIAN_CI => 140,
        self::UCS2_PERSIAN_CI => 144,
        self::UCS2_POLISH_CI => 133,
        self::UCS2_ROMANIAN_CI => 131,
        self::UCS2_ROMAN_CI => 143,
        self::UCS2_SINHALA_CI => 147,
        self::UCS2_SLOVAK_CI => 141,
        self::UCS2_SLOVENIAN_CI => 132,
        self::UCS2_SPANISH2_CI => 142,
        self::UCS2_SPANISH_CI => 135,
        self::UCS2_SWEDISH_CI => 136,
        self::UCS2_TURKISH_CI => 137,
        self::UCS2_UNICODE_520_CI => 150,
        self::UCS2_UNICODE_CI => 128,
        self::UCS2_VIETNAMESE_CI => 151,
        self::UJIS_BIN => 91,
        self::UJIS_JAPANESE_CI => 12,
        self::UTF16LE_BIN => 62,
        self::UTF16LE_GENERAL_CI => 56,
        self::UTF16_BIN => 55,
        self::UTF16_CROATIAN_CI => 122,
        self::UTF16_CZECH_CI => 111,
        self::UTF16_DANISH_CI => 112,
        self::UTF16_ESPERANTO_CI => 118,
        self::UTF16_ESTONIAN_CI => 107,
        self::UTF16_GENERAL_CI => 54,
        self::UTF16_GERMAN2_CI => 121,
        self::UTF16_HUNGARIAN_CI => 119,
        self::UTF16_ICELANDIC_CI => 102,
        self::UTF16_LATVIAN_CI => 103,
        self::UTF16_LITHUANIAN_CI => 113,
        self::UTF16_PERSIAN_CI => 117,
        self::UTF16_POLISH_CI => 106,
        self::UTF16_ROMANIAN_CI => 104,
        self::UTF16_ROMAN_CI => 116,
        self::UTF16_SINHALA_CI => 120,
        self::UTF16_SLOVAK_CI => 114,
        self::UTF16_SLOVENIAN_CI => 105,
        self::UTF16_SPANISH2_CI => 115,
        self::UTF16_SPANISH_CI => 108,
        self::UTF16_SWEDISH_CI => 109,
        self::UTF16_TURKISH_CI => 110,
        self::UTF16_UNICODE_520_CI => 123,
        self::UTF16_UNICODE_CI => 101,
        self::UTF16_VIETNAMESE_CI => 124,
        self::UTF32_BIN => 61,
        self::UTF32_CROATIAN_CI => 181,
        self::UTF32_CZECH_CI => 170,
        self::UTF32_DANISH_CI => 171,
        self::UTF32_ESPERANTO_CI => 177,
        self::UTF32_ESTONIAN_CI => 166,
        self::UTF32_GENERAL_CI => 60,
        self::UTF32_GERMAN2_CI => 180,
        self::UTF32_HUNGARIAN_CI => 178,
        self::UTF32_ICELANDIC_CI => 161,
        self::UTF32_LATVIAN_CI => 162,
        self::UTF32_LITHUANIAN_CI => 172,
        self::UTF32_PERSIAN_CI => 176,
        self::UTF32_POLISH_CI => 165,
        self::UTF32_ROMANIAN_CI => 163,
        self::UTF32_ROMAN_CI => 175,
        self::UTF32_SINHALA_CI => 179,
        self::UTF32_SLOVAK_CI => 173,
        self::UTF32_SLOVENIAN_CI => 164,
        self::UTF32_SPANISH2_CI => 174,
        self::UTF32_SPANISH_CI => 167,
        self::UTF32_SWEDISH_CI => 168,
        self::UTF32_TURKISH_CI => 169,
        self::UTF32_UNICODE_520_CI => 182,
        self::UTF32_UNICODE_CI => 160,
        self::UTF32_VIETNAMESE_CI => 183,
        self::UTF8MB3_BIN => 83,
        self::UTF8MB4_BIN => 46,
        self::UTF8MB4_BOSNIAN_0900_AI_CI => 316,
        self::UTF8MB4_BOSNIAN_0900_AS_CS => 317,
        self::UTF8MB4_BULGARIAN_0900_AI_CI => 318,
        self::UTF8MB4_BULGARIAN_0900_AS_CS => 319,
        self::UTF8MB4_CHINESE_0900_AS_CS => 308,
        self::UTF8MB4_CLASSICAL_LATIN_0900_AI_CI => 271,
        self::UTF8MB4_CLASSICAL_LATIN_0900_AS_CS => 294,
        self::UTF8MB4_CROATIAN_0900_AI_CI => 275,
        self::UTF8MB4_CROATIAN_0900_AS_CS => 298,
        self::UTF8MB4_CROATIAN_CI => 245,
        self::UTF8MB4_CZECH_0900_AI_CI => 266,
        self::UTF8MB4_CZECH_0900_AS_CS => 289,
        self::UTF8MB4_CZECH_CI => 234,
        self::UTF8MB4_DANISH_0900_AI_CI => 267,
        self::UTF8MB4_DANISH_0900_AS_CS => 290,
        self::UTF8MB4_DANISH_CI => 235,
        self::UTF8MB4_ESPERANTO_0900_AI_CI => 273,
        self::UTF8MB4_ESPERANTO_0900_AS_CS => 296,
        self::UTF8MB4_ESPERANTO_CI => 241,
        self::UTF8MB4_ESTONIAN_0900_AI_CI => 262,
        self::UTF8MB4_ESTONIAN_0900_AS_CS => 285,
        self::UTF8MB4_ESTONIAN_CI => 230,
        self::UTF8MB4_GALICIAN_0900_AI_CI => 320,
        self::UTF8MB4_GALICIAN_0900_AS_CS => 321,
        self::UTF8MB4_GENERAL_0900_AI_CI => 255,
        self::UTF8MB4_GENERAL_0900_AS_CI => 305,
        self::UTF8MB4_GENERAL_0900_AS_CS => 278,
        self::UTF8MB4_GENERAL_0900_BIN => 309,
        self::UTF8MB4_GENERAL_CI => 45,
        self::UTF8MB4_GERMAN2_CI => 244,
        self::UTF8MB4_GERMAN_PHONE_BOOK_0900_AI_CI => 256,
        self::UTF8MB4_GERMAN_PHONE_BOOK_0900_AS_CS => 279,
        self::UTF8MB4_HUNGARIAN_0900_AI_CI => 274,
        self::UTF8MB4_HUNGARIAN_0900_AS_CS => 297,
        self::UTF8MB4_HUNGARIAN_CI => 242,
        self::UTF8MB4_ICELANDIC_0900_AI_CI => 257,
        self::UTF8MB4_ICELANDIC_0900_AS_CS => 280,
        self::UTF8MB4_ICELANDIC_CI => 225,
        self::UTF8MB4_JAPANESE_0900_AS_CS => 303,
        self::UTF8MB4_JAPANESE_0900_AS_CS_KS => 304,
        self::UTF8MB4_LATVIAN_0900_AI_CI => 258,
        self::UTF8MB4_LATVIAN_0900_AS_CS => 281,
        self::UTF8MB4_LATVIAN_CI => 226,
        self::UTF8MB4_LITHUANIAN_0900_AI_CI => 268,
        self::UTF8MB4_LITHUANIAN_0900_AS_CS => 291,
        self::UTF8MB4_LITHUANIAN_CI => 236,
        self::UTF8MB4_MONGOLIAN_CYRILLIC_0900_AI_CI => 322,
        self::UTF8MB4_MONGOLIAN_CYRILLIC_0900_AS_CS => 323,
        self::UTF8MB4_NORWEGIAN_BOKMAL_0900_AI_CI => 310,
        self::UTF8MB4_NORWEGIAN_BOKMAL_0900_AS_CS => 311,
        self::UTF8MB4_NORWEGIAN_NYNORSK_0900_AI_CI => 312,
        self::UTF8MB4_NORWEGIAN_NYNORSK_0900_AS_CS => 313,
        self::UTF8MB4_PERSIAN_CI => 240,
        self::UTF8MB4_POLISH_0900_AI_CI => 261,
        self::UTF8MB4_POLISH_0900_AS_CS => 284,
        self::UTF8MB4_POLISH_CI => 229,
        self::UTF8MB4_ROMANIAN_0900_AI_CI => 259,
        self::UTF8MB4_ROMANIAN_0900_AS_CS => 282,
        self::UTF8MB4_ROMANIAN_CI => 227,
        self::UTF8MB4_ROMAN_CI => 239,
        self::UTF8MB4_RUSSIAN_0900_AI_CI => 306,
        self::UTF8MB4_RUSSIAN_0900_AS_CS => 307,
        self::UTF8MB4_SERBIAN_LATIN_0900_AI_CI => 314,
        self::UTF8MB4_SERBIAN_LATIN_0900_AS_CS => 315,
        self::UTF8MB4_SINHALA_CI => 243,
        self::UTF8MB4_SLOVAK_0900_AI_CI => 269,
        self::UTF8MB4_SLOVAK_0900_AS_CS => 292,
        self::UTF8MB4_SLOVAK_CI => 237,
        self::UTF8MB4_SLOVENIAN_0900_AI_CI => 260,
        self::UTF8MB4_SLOVENIAN_0900_AS_CS => 283,
        self::UTF8MB4_SLOVENIAN_CI => 228,
        self::UTF8MB4_SPANISH2_CI => 238,
        self::UTF8MB4_SPANISH_0900_AI_CI => 263,
        self::UTF8MB4_SPANISH_0900_AS_CS => 286,
        self::UTF8MB4_SPANISH_CI => 231,
        self::UTF8MB4_SWEDISH_0900_AI_CI => 264,
        self::UTF8MB4_SWEDISH_0900_AS_CS => 287,
        self::UTF8MB4_SWEDISH_CI => 232,
        self::UTF8MB4_TRADITIONAL_SPANISH_0900_AI_CI => 270,
        self::UTF8MB4_TRADITIONAL_SPANISH_0900_AS_CS => 293,
        self::UTF8MB4_TURKISH_0900_AI_CI => 265,
        self::UTF8MB4_TURKISH_0900_AS_CS => 288,
        self::UTF8MB4_TURKISH_CI => 233,
        self::UTF8MB4_UNICODE_520_CI => 246,
        self::UTF8MB4_UNICODE_CI => 224,
        self::UTF8MB4_VIETNAMESE_0900_AI_CI => 277,
        self::UTF8MB4_VIETNAMESE_0900_AS_CS => 300,
        self::UTF8MB4_VIETNAMESE_CI => 247,
        self::UTF8_BIN => 83,
        self::UTF8_CROATIAN_CI => 213,
        self::UTF8_CZECH_CI => 202,
        self::UTF8_DANISH_CI => 203,
        self::UTF8_ESPERANTO_CI => 209,
        self::UTF8_ESTONIAN_CI => 198,
        self::UTF8_GENERAL_CI => 33,
        self::UTF8_GENERAL_MYSQL500_CI => 223,
        self::UTF8_GERMAN2_CI => 212,
        self::UTF8_HUNGARIAN_CI => 210,
        self::UTF8_ICELANDIC_CI => 193,
        self::UTF8_LATVIAN_CI => 194,
        self::UTF8_LITHUANIAN_CI => 204,
        self::UTF8_PERSIAN_CI => 208,
        self::UTF8_POLISH_CI => 197,
        self::UTF8_ROMANIAN_CI => 195,
        self::UTF8_ROMAN_CI => 207,
        self::UTF8_SINHALA_CI => 211,
        self::UTF8_SLOVAK_CI => 205,
        self::UTF8_SLOVENIAN_CI => 196,
        self::UTF8_SPANISH2_CI => 206,
        self::UTF8_SPANISH_CI => 199,
        self::UTF8_SWEDISH_CI => 200,
        self::UTF8_TOLOWER_CI => 76,
        self::UTF8_TURKISH_CI => 201,
        self::UTF8_UNICODE_520_CI => 214,
        self::UTF8_UNICODE_CI => 192,
        self::UTF8_VIETNAMESE_CI => 215,
    ];

    /** @var array<string, string> */
    private static array $charsets = [
        self::ARMSCII8_BIN => Charset::ARMSCII8,
        self::ARMSCII8_GENERAL_CI => Charset::ARMSCII8,
        self::ASCII_BIN => Charset::ASCII,
        self::ASCII_GENERAL_CI => Charset::ASCII,
        self::BIG5_BIN => Charset::BIG5,
        self::BIG5_CHINESE_CI => Charset::BIG5,
        self::BINARY => Charset::BINARY,
        self::CP1250_BIN => Charset::CP1250,
        self::CP1250_CROATIAN_CI => Charset::CP1250,
        self::CP1250_CZECH_CS => Charset::CP1250,
        self::CP1250_GENERAL_CI => Charset::CP1250,
        self::CP1250_POLISH_CI => Charset::CP1250,
        self::CP1251_BIN => Charset::CP1251,
        self::CP1251_BULGARIAN_CI => Charset::CP1251,
        self::CP1251_GENERAL_CI => Charset::CP1251,
        self::CP1251_GENERAL_CS => Charset::CP1251,
        self::CP1251_UKRAINIAN_CI => Charset::CP1251,
        self::CP1256_BIN => Charset::CP1256,
        self::CP1256_GENERAL_CI => Charset::CP1256,
        self::CP1257_BIN => Charset::CP1257,
        self::CP1257_GENERAL_CI => Charset::CP1257,
        self::CP1257_LITHUANIAN_CI => Charset::CP1257,
        self::CP850_BIN => Charset::CP850,
        self::CP850_GENERAL_CI => Charset::CP850,
        self::CP852_BIN => Charset::CP852,
        self::CP852_GENERAL_CI => Charset::CP852,
        self::CP866_BIN => Charset::CP866,
        self::CP866_GENERAL_CI => Charset::CP866,
        self::CP932_BIN => Charset::CP932,
        self::CP932_JAPANESE_CI => Charset::CP932,
        self::DEC8_BIN => Charset::DEC8,
        self::DEC8_SWEDISH_CI => Charset::DEC8,
        self::EUCJPMS_BIN => Charset::EUCJPMS,
        self::EUCJPMS_JAPANESE_CI => Charset::EUCJPMS,
        self::EUCKR_BIN => Charset::EUCKR,
        self::EUCKR_KOREAN_CI => Charset::EUCKR,
        self::GB18030_BIN => Charset::GB18030,
        self::GB18030_CHINESE_CI => Charset::GB18030,
        self::GB18030_UNICODE_520_CI => Charset::GB18030,
        self::GB2312_BIN => Charset::GB2312,
        self::GB2312_CHINESE_CI => Charset::GB2312,
        self::GBK_BIN => Charset::GBK,
        self::GBK_CHINESE_CI => Charset::GBK,
        self::GEOSTD8_BIN => Charset::GEOSTD8,
        self::GEOSTD8_GENERAL_CI => Charset::GEOSTD8,
        self::GREEK_BIN => Charset::GREEK,
        self::GREEK_GENERAL_CI => Charset::GREEK,
        self::HEBREW_BIN => Charset::HEBREW,
        self::HEBREW_GENERAL_CI => Charset::HEBREW,
        self::HP8_BIN => Charset::HP8,
        self::HP8_ENGLISH_CI => Charset::HP8,
        self::KEYBCS2_BIN => Charset::KEYBCS2,
        self::KEYBCS2_GENERAL_CI => Charset::KEYBCS2,
        self::KOI8R_BIN => Charset::KOI8R,
        self::KOI8R_GENERAL_CI => Charset::KOI8R,
        self::KOI8U_BIN => Charset::KOI8U,
        self::KOI8U_GENERAL_CI => Charset::KOI8U,
        self::LATIN1_BIN => Charset::LATIN1,
        self::LATIN1_DANISH_CI => Charset::LATIN1,
        self::LATIN1_GENERAL_CI => Charset::LATIN1,
        self::LATIN1_GENERAL_CS => Charset::LATIN1,
        self::LATIN1_GERMAN1_CI => Charset::LATIN1,
        self::LATIN1_GERMAN2_CI => Charset::LATIN1,
        self::LATIN1_SPANISH_CI => Charset::LATIN1,
        self::LATIN1_SWEDISH_CI => Charset::LATIN1,
        self::LATIN2_BIN => Charset::LATIN2,
        self::LATIN2_CROATIAN_CI => Charset::LATIN2,
        self::LATIN2_CZECH_CS => Charset::LATIN2,
        self::LATIN2_GENERAL_CI => Charset::LATIN2,
        self::LATIN2_HUNGARIAN_CI => Charset::LATIN2,
        self::LATIN5_BIN => Charset::LATIN5,
        self::LATIN5_TURKISH_CI => Charset::LATIN5,
        self::LATIN7_BIN => Charset::LATIN7,
        self::LATIN7_ESTONIAN_CS => Charset::LATIN7,
        self::LATIN7_GENERAL_CI => Charset::LATIN7,
        self::LATIN7_GENERAL_CS => Charset::LATIN7,
        self::MACCE_BIN => Charset::MACCE,
        self::MACCE_GENERAL_CI => Charset::MACCE,
        self::MACROMAN_BIN => Charset::MACROMAN,
        self::MACROMAN_GENERAL_CI => Charset::MACROMAN,
        self::SJIS_BIN => Charset::SJIS,
        self::SJIS_JAPANESE_CI => Charset::SJIS,
        self::SWE7_BIN => Charset::SWE7,
        self::SWE7_SWEDISH_CI => Charset::SWE7,
        self::TIS620_BIN => Charset::TIS620,
        self::TIS620_THAI_CI => Charset::TIS620,
        self::UCS2_BIN => Charset::UCS2,
        self::UCS2_CROATIAN_CI => Charset::UCS2,
        self::UCS2_CZECH_CI => Charset::UCS2,
        self::UCS2_DANISH_CI => Charset::UCS2,
        self::UCS2_ESPERANTO_CI => Charset::UCS2,
        self::UCS2_ESTONIAN_CI => Charset::UCS2,
        self::UCS2_GENERAL_CI => Charset::UCS2,
        self::UCS2_GENERAL_MYSQL500_CI => Charset::UCS2,
        self::UCS2_GERMAN2_CI => Charset::UCS2,
        self::UCS2_HUNGARIAN_CI => Charset::UCS2,
        self::UCS2_ICELANDIC_CI => Charset::UCS2,
        self::UCS2_LATVIAN_CI => Charset::UCS2,
        self::UCS2_LITHUANIAN_CI => Charset::UCS2,
        self::UCS2_PERSIAN_CI => Charset::UCS2,
        self::UCS2_POLISH_CI => Charset::UCS2,
        self::UCS2_ROMANIAN_CI => Charset::UCS2,
        self::UCS2_ROMAN_CI => Charset::UCS2,
        self::UCS2_SINHALA_CI => Charset::UCS2,
        self::UCS2_SLOVAK_CI => Charset::UCS2,
        self::UCS2_SLOVENIAN_CI => Charset::UCS2,
        self::UCS2_SPANISH2_CI => Charset::UCS2,
        self::UCS2_SPANISH_CI => Charset::UCS2,
        self::UCS2_SWEDISH_CI => Charset::UCS2,
        self::UCS2_TURKISH_CI => Charset::UCS2,
        self::UCS2_UNICODE_520_CI => Charset::UCS2,
        self::UCS2_UNICODE_CI => Charset::UCS2,
        self::UCS2_VIETNAMESE_CI => Charset::UCS2,
        self::UJIS_BIN => Charset::UJIS,
        self::UJIS_JAPANESE_CI => Charset::UJIS,
        self::UTF16LE_BIN => Charset::UTF16LE,
        self::UTF16LE_GENERAL_CI => Charset::UTF16LE,
        self::UTF16_BIN => Charset::UTF16,
        self::UTF16_CROATIAN_CI => Charset::UTF16,
        self::UTF16_CZECH_CI => Charset::UTF16,
        self::UTF16_DANISH_CI => Charset::UTF16,
        self::UTF16_ESPERANTO_CI => Charset::UTF16,
        self::UTF16_ESTONIAN_CI => Charset::UTF16,
        self::UTF16_GENERAL_CI => Charset::UTF16,
        self::UTF16_GERMAN2_CI => Charset::UTF16,
        self::UTF16_HUNGARIAN_CI => Charset::UTF16,
        self::UTF16_ICELANDIC_CI => Charset::UTF16,
        self::UTF16_LATVIAN_CI => Charset::UTF16,
        self::UTF16_LITHUANIAN_CI => Charset::UTF16,
        self::UTF16_PERSIAN_CI => Charset::UTF16,
        self::UTF16_POLISH_CI => Charset::UTF16,
        self::UTF16_ROMANIAN_CI => Charset::UTF16,
        self::UTF16_ROMAN_CI => Charset::UTF16,
        self::UTF16_SINHALA_CI => Charset::UTF16,
        self::UTF16_SLOVAK_CI => Charset::UTF16,
        self::UTF16_SLOVENIAN_CI => Charset::UTF16,
        self::UTF16_SPANISH2_CI => Charset::UTF16,
        self::UTF16_SPANISH_CI => Charset::UTF16,
        self::UTF16_SWEDISH_CI => Charset::UTF16,
        self::UTF16_TURKISH_CI => Charset::UTF16,
        self::UTF16_UNICODE_520_CI => Charset::UTF16,
        self::UTF16_UNICODE_CI => Charset::UTF16,
        self::UTF16_VIETNAMESE_CI => Charset::UTF16,
        self::UTF32_BIN => Charset::UTF32,
        self::UTF32_CROATIAN_CI => Charset::UTF32,
        self::UTF32_CZECH_CI => Charset::UTF32,
        self::UTF32_DANISH_CI => Charset::UTF32,
        self::UTF32_ESPERANTO_CI => Charset::UTF32,
        self::UTF32_ESTONIAN_CI => Charset::UTF32,
        self::UTF32_GENERAL_CI => Charset::UTF32,
        self::UTF32_GERMAN2_CI => Charset::UTF32,
        self::UTF32_HUNGARIAN_CI => Charset::UTF32,
        self::UTF32_ICELANDIC_CI => Charset::UTF32,
        self::UTF32_LATVIAN_CI => Charset::UTF32,
        self::UTF32_LITHUANIAN_CI => Charset::UTF32,
        self::UTF32_PERSIAN_CI => Charset::UTF32,
        self::UTF32_POLISH_CI => Charset::UTF32,
        self::UTF32_ROMANIAN_CI => Charset::UTF32,
        self::UTF32_ROMAN_CI => Charset::UTF32,
        self::UTF32_SINHALA_CI => Charset::UTF32,
        self::UTF32_SLOVAK_CI => Charset::UTF32,
        self::UTF32_SLOVENIAN_CI => Charset::UTF32,
        self::UTF32_SPANISH2_CI => Charset::UTF32,
        self::UTF32_SPANISH_CI => Charset::UTF32,
        self::UTF32_SWEDISH_CI => Charset::UTF32,
        self::UTF32_TURKISH_CI => Charset::UTF32,
        self::UTF32_UNICODE_520_CI => Charset::UTF32,
        self::UTF32_UNICODE_CI => Charset::UTF32,
        self::UTF32_VIETNAMESE_CI => Charset::UTF32,
        self::UTF8MB4_GENERAL_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_GENERAL_0900_AS_CI => Charset::UTF8MB4,
        self::UTF8MB4_GENERAL_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_GENERAL_0900_BIN => Charset::UTF8MB4,
        self::UTF8MB4_BIN => Charset::UTF8MB4,
        self::UTF8MB4_BOSNIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_BOSNIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_BULGARIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_BULGARIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_CROATIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_CZECH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_CZECH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_CZECH_CI => Charset::UTF8MB4,
        self::UTF8MB4_DANISH_CI => Charset::UTF8MB4,
        self::UTF8MB4_DANISH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_DANISH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_GERMAN_PHONE_BOOK_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_GERMAN_PHONE_BOOK_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_ESPERANTO_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_ESPERANTO_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_ESPERANTO_CI => Charset::UTF8MB4,
        self::UTF8MB4_ESTONIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_SPANISH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_SPANISH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_TRADITIONAL_SPANISH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_TRADITIONAL_SPANISH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_ESTONIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_ESTONIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_GALICIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_GALICIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_GENERAL_CI => Charset::UTF8MB4,
        self::UTF8MB4_GERMAN2_CI => Charset::UTF8MB4,
        self::UTF8MB4_CROATIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_CROATIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_HUNGARIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_HUNGARIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_HUNGARIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_ICELANDIC_CI => Charset::UTF8MB4,
        self::UTF8MB4_ICELANDIC_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_ICELANDIC_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_JAPANESE_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_JAPANESE_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_JAPANESE_0900_AS_CS_KS => Charset::UTF8MB4,
        self::UTF8MB4_LATVIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_CLASSICAL_LATIN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_CLASSICAL_LATIN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_LITHUANIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_LITHUANIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_LITHUANIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_LATVIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_LATVIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_MONGOLIAN_CYRILLIC_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_MONGOLIAN_CYRILLIC_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_NORWEGIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_NORWEGIAN_BOKMAL_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_NORWEGIAN_BOKMAL_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_NORWEGIAN_NYNORSK_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_NORWEGIAN_NYNORSK_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_PERSIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_POLISH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_POLISH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_POLISH_CI => Charset::UTF8MB4,
        self::UTF8MB4_ROMANIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_ROMAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_ROMANIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_ROMANIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_RUSSIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_RUSSIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_SINHALA_CI => Charset::UTF8MB4,
        self::UTF8MB4_SERBIAN_LATIN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_SERBIAN_LATIN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_SLOVAK_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_SLOVAK_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_SLOVAK_CI => Charset::UTF8MB4,
        self::UTF8MB4_SLOVENIAN_CI => Charset::UTF8MB4,
        self::UTF8MB4_SLOVENIAN_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_SLOVENIAN_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_SPANISH2_CI => Charset::UTF8MB4,
        self::UTF8MB4_SPANISH_CI => Charset::UTF8MB4,
        self::UTF8MB4_SWEDISH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_SWEDISH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_SWEDISH_CI => Charset::UTF8MB4,
        self::UTF8MB4_TURKISH_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_TURKISH_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_TURKISH_CI => Charset::UTF8MB4,
        self::UTF8MB4_UNICODE_520_CI => Charset::UTF8MB4,
        self::UTF8MB4_UNICODE_CI => Charset::UTF8MB4,
        self::UTF8MB4_VIETNAMESE_CI => Charset::UTF8MB4,
        self::UTF8MB4_VIETNAMESE_0900_AI_CI => Charset::UTF8MB4,
        self::UTF8MB4_VIETNAMESE_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8MB4_CHINESE_0900_AS_CS => Charset::UTF8MB4,
        self::UTF8_BIN => Charset::UTF8,
        self::UTF8_CROATIAN_CI => Charset::UTF8,
        self::UTF8_CZECH_CI => Charset::UTF8,
        self::UTF8_DANISH_CI => Charset::UTF8,
        self::UTF8_ESPERANTO_CI => Charset::UTF8,
        self::UTF8_ESTONIAN_CI => Charset::UTF8,
        self::UTF8_GENERAL_CI => Charset::UTF8,
        self::UTF8_GENERAL_MYSQL500_CI => Charset::UTF8,
        self::UTF8_GERMAN2_CI => Charset::UTF8,
        self::UTF8_HUNGARIAN_CI => Charset::UTF8,
        self::UTF8_ICELANDIC_CI => Charset::UTF8,
        self::UTF8_LATVIAN_CI => Charset::UTF8,
        self::UTF8_LITHUANIAN_CI => Charset::UTF8,
        self::UTF8_PERSIAN_CI => Charset::UTF8,
        self::UTF8_POLISH_CI => Charset::UTF8,
        self::UTF8_ROMANIAN_CI => Charset::UTF8,
        self::UTF8_ROMAN_CI => Charset::UTF8,
        self::UTF8_SINHALA_CI => Charset::UTF8,
        self::UTF8_SLOVAK_CI => Charset::UTF8,
        self::UTF8_SLOVENIAN_CI => Charset::UTF8,
        self::UTF8_SPANISH2_CI => Charset::UTF8,
        self::UTF8_SPANISH_CI => Charset::UTF8,
        self::UTF8_SWEDISH_CI => Charset::UTF8,
        self::UTF8_TOLOWER_CI => Charset::UTF8,
        self::UTF8_TURKISH_CI => Charset::UTF8,
        self::UTF8_UNICODE_520_CI => Charset::UTF8,
        self::UTF8_UNICODE_CI => Charset::UTF8,
        self::UTF8_VIETNAMESE_CI => Charset::UTF8,

        self::UTF8MB3_BIN => Charset::UTF8MB3,
        self::UTF8_BENGALI_STANDARD_CI => Charset::UTF8,
        self::UTF8_BENGALI_TRADITIONAL_CI => Charset::UTF8,
    ];

    /**
     * @param scalar|null $value
     */
    public static function tryCreate($value): ?self
    {
        try {
            return new self((string) $value);
        } catch (InvalidEnumValueException $e) {
            return null;
        }
    }

    public static function getById(int $id): self
    {
        $name = array_search($id, self::$ids, true);
        if ($name === false) {
            throw new InvalidDefinitionException("Unknown collation id: $id");
        }

        return new self($name);
    }

    public static function getNameById(int $id): ?string
    {
        $name = array_search($id, self::$ids, true);

        return $name !== false ? $name : null;
    }

    public function getId(): int
    {
        return self::$ids[$this->getValue()] ?? 0;
    }

    public function getCharsetName(): string
    {
        $value = $this->getValue();

        return self::$charsets[$value] ?? (string) Str::before($value, '_');
    }

}
