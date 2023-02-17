<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: euccn eucjp eucjpwin euckr euctw hakre jisms koi8r koi8t koi8u sjismac sjiswin ucs2be ucs2le ucs4be ucs4le utf7imap

namespace Dogma\Language;

use Dogma\Enum\StringEnum;
use function str_replace;
use function strtolower;
use function strtoupper;

/**
 * Encoding codes accepted by mbstring and iconv (except BINARY; not all of them)
 */
class Encoding extends StringEnum
{

    public const BINARY = 'BINARY';

    public const ASCII = 'ASCII';

    public const UTF_8 = 'UTF-8';

    public const UTF_7 = 'UTF-7';
    public const UTF_7_IMAP = 'UTF7-IMAP';

    public const UTF_16 = 'UTF-16';
    public const UTF_16BE = 'UTF-16BE';
    public const UTF_16LE = 'UTF-16LE';

    public const UTF_32 = 'UTF-32';
    public const UTF_32BE = 'UTF-32BE';
    public const UTF_32LE = 'UTF-32LE';

    public const UCS_2 = 'UCS-2';
    public const UCS_2BE = 'UCS-2BE';
    public const UCS_2LE = 'UCS-2LE';

    public const UCS_4 = 'UCS-4';
    public const UCS_4BE = 'UCS-4BE';
    public const UCS_4LE = 'UCS-4LE';

    public const ISO_8859_1 = 'ISO-8859-1'; // Latin-1 Western European
    public const ISO_8859_2 = 'ISO-8859-2'; // Latin-2 Central European
    public const ISO_8859_3 = 'ISO-8859-3'; // Latin-3 South European
    public const ISO_8859_4 = 'ISO-8859-4'; // Latin-4 North European
    public const ISO_8859_5 = 'ISO-8859-5'; // Latin/Cyrillic
    public const ISO_8859_6 = 'ISO-8859-6'; // Latin/Arabic
    public const ISO_8859_7 = 'ISO-8859-7'; // Latin/Greek
    public const ISO_8859_8 = 'ISO-8859-8'; // Latin/Hebrew
    public const ISO_8859_9 = 'ISO-8859-9'; // Latin-5 Turkish
    public const ISO_8859_10 = 'ISO-8859-10'; // Latin-6 Nordic
    public const ISO_8859_11 = 'ISO-8859-11'; // Latin/Thai
    public const ISO_8859_13 = 'ISO-8859-13'; // Latin-7 Baltic Rim
    public const ISO_8859_14 = 'ISO-8859-14'; // Latin-8 Celtic
    public const ISO_8859_15 = 'ISO-8859-15'; // Latin-9
    public const ISO_8859_16 = 'ISO-8859-16'; // Latin-10 South-Eastern European

    public const WINDOWS_1250 = 'WINDOWS-1250'; // Latin 2 / Central European
    public const WINDOWS_1251 = 'WINDOWS-1251'; // Cyrillic
    public const WINDOWS_1252 = 'WINDOWS-1252'; // Latin 1 / Western European
    public const WINDOWS_1253 = 'WINDOWS-1253'; // Greek
    public const WINDOWS_1254 = 'WINDOWS-1254'; // Turkish
    public const WINDOWS_1255 = 'WINDOWS-1255'; // Hebrew
    public const WINDOWS_1256 = 'WINDOWS-1256'; // Arabic
    public const WINDOWS_1257 = 'WINDOWS-1257'; // Baltic
    public const WINDOWS_1258 = 'WINDOWS-1258'; // Vietnamese

    public const CP850 = 'CP850'; // DOS Latin 1 Western European
    public const CP852 = 'CP852'; // DOS Latin 2 Central European
    public const CP862 = 'CP862'; // DOS Hebrew
    public const CP866 = 'CP866'; // DOS Cyrillic
    public const CP932 = 'CP932'; // IBM SJIS
    public const CP936 = 'CP936'; // IBM Simplified Chinese
    public const CP950 = 'CP950'; // MS BIG-5

    public const CP50220 = 'CP50220';
    public const CP50221 = 'CP50221';
    public const CP50222 = 'CP50222';
    public const CP51932 = 'CP51932';

    public const EUC_JP = 'EUC-JP';
    public const EUC_JP_WIN = 'EUC-JP-WIN';
    public const EUC_JP_2004 = 'EUC-JP-2004';
    public const EUC_CN = 'EUC-CN';
    public const EUC_TW = 'EUC-TW';
    public const EUC_KR = 'EUC-KR';

    public const JIS = 'JIS';
    public const JIS_MS = 'JIS-MS';

    public const SJIS = 'SJIS';
    public const SJIS_WIN = 'SJIS-WIN';
    public const SJIS_MAC = 'SJIS-MAC';
    public const SJIS_2004 = 'SJIS-2004';

    public const ISO_2022_JP = 'ISO-2022-JP';
    public const ISO_2022_JP_MS = 'ISO-2022-JP-MS';
    public const ISO_2022_JP_2004 = 'ISO-2022-JP-2004';
    public const ISO_2022_KR = 'ISO-2022-KR';

    public const KOI8_R = 'KOI8-R';
    public const KOI8_U = 'KOI8-U';
    public const KOI8_T = 'KOI8-T';

    public const GB18030 = 'GB18030';

    public const BIG_5 = 'BIG-5';

    public const UHC = 'UHC';

    public const HZ = 'HZ';

    public const ARMSCII_8 = 'ARMSCII-8';

    // iconv aliases: https://gist.github.com/hakre/4188459
    /** @var string[] */
    private static $aliases = [
        'bin' => self::BINARY,
        'binary' => self::BINARY,
        'ascii' => self::ASCII,
        'utf8' => self::UTF_8,
        'utf7' => self::UTF_7,
        'utf7imap' => self::UTF_7_IMAP,
        'utf16' => self::UTF_16,
        'utf16be' => self::UTF_16BE,
        'utf16le' => self::UTF_16LE,
        'utf32' => self::UTF_32,
        'utf32be' => self::UTF_32BE,
        'utf32le' => self::UTF_32LE,
        'ucs2' => self::UCS_2,
        'ucs2be' => self::UCS_2BE,
        'ucs2le' => self::UCS_2LE,
        'ucs4' => self::UCS_4,
        'ucs4be' => self::UCS_4BE,
        'ucs4le' => self::UCS_4LE,
        'iso88591' => self::ISO_8859_1,
        'latin1' => self::ISO_8859_1,
        'iso88592' => self::ISO_8859_2,
        'latin2' => self::ISO_8859_2,
        'iso88593' => self::ISO_8859_3,
        'latin3' => self::ISO_8859_3,
        'iso88594' => self::ISO_8859_4,
        'latin4' => self::ISO_8859_4,
        'iso88595' => self::ISO_8859_5, // Latin/Cyrillic
        'iso88596' => self::ISO_8859_6, // Latin/Arabic
        'iso88597' => self::ISO_8859_7, // Latin/Greek
        'iso88598' => self::ISO_8859_8, // Latin/Hebrew
        'iso88599' => self::ISO_8859_9,
        'latin5' => self::ISO_8859_9,
        'iso885910' => self::ISO_8859_10,
        'latin6' => self::ISO_8859_10,
        'iso885911' => self::ISO_8859_11, // Latin/Thai
        'iso885913' => self::ISO_8859_13,
        'latin7' => self::ISO_8859_13,
        'iso885914' => self::ISO_8859_14,
        'latin8' => self::ISO_8859_14,
        'iso885915' => self::ISO_8859_15,
        'latin9' => self::ISO_8859_15,
        'iso885916' => self::ISO_8859_16,
        'latin10' => self::ISO_8859_16,
        'windows1250' => self::WINDOWS_1250, // Latin 2 / Central European
        'windows1251' => self::WINDOWS_1251, // Cyrillic
        'windows1252' => self::WINDOWS_1252, // Latin 1 / Western European
        'windows1253' => self::WINDOWS_1253, // Greek
        'windows1254' => self::WINDOWS_1254, // Turkish
        'windows1255' => self::WINDOWS_1255, // Hebrew
        'windows1256' => self::WINDOWS_1256, // Arabic
        'windows1257' => self::WINDOWS_1257, // Baltic
        'windows1258' => self::WINDOWS_1258, // Vietnamese
        'cp850' => self::CP850, // DOS Latin 1 Western European
        'cp852' => self::CP852, // DOS Latin 2 Central European
        'cp862' => self::CP862, // DOS Hebrew
        'cp866' => self::CP866, // DOS Cyrillic
        'cp932' => self::CP932, // IBM SJIS
        'cp936' => self::CP936, // IBM Simplified Chinese
        'cp950' => self::CP950, // MS BIG-5
        'cp50220' => self::CP50220,
        'cp50221' => self::CP50221,
        'cp50222' => self::CP50222,
        'cp51932' => self::CP51932,
        'eucjp' => self::EUC_JP,
        'eucjpwin' => self::EUC_JP_WIN,
        'eucjp2004' => self::EUC_JP_2004,
        'euccn' => self::EUC_CN,
        'euctw' => self::EUC_TW,
        'euckr' => self::EUC_KR,
        'jis' => self::JIS,
        'jisms' => self::JIS_MS,
        'sjis' => self::SJIS,
        'sjiswin' => self::SJIS_WIN,
        'sjismac' => self::SJIS_MAC,
        'sjis2004' => self::SJIS_2004,
        'iso2022jp' => self::ISO_2022_JP,
        'iso2022jpms' => self::ISO_2022_JP_MS,
        'iso2022jp2004' => self::ISO_2022_JP_2004,
        'iso2022kr' => self::ISO_2022_KR,
        'koi8r' => self::KOI8_R,
        'koi8u' => self::KOI8_U,
        'koi8t' => self::KOI8_T,
        'gb18030' => self::GB18030,
        'big5' => self::BIG_5,
        'uhc' => self::UHC,
        'hz' => self::HZ,
        'armscii8' => self::ARMSCII_8,
    ];

    /** @var string[] */
    private static $unused = [
        self::ISO_8859_1 => "~[\x7F-\xFF]~",
        self::ISO_8859_2 => "~[\x7F-\xFF]~",
        self::ISO_8859_3 => "~[\x7F-\xFF\xA5\xAE\xBE\xC3\xD0\xE3\xF0]~",
        self::ISO_8859_4 => "~[\x7F-\xFF]~",
        self::ISO_8859_5 => "~[\x7F-\xFF]~",
        self::ISO_8859_6 => "~[\x7F-\xFF\xA1-\xA3\xA5-\xAB\xAE-\xBA\xBC-\xBE\xC0\xDB-\xDF\xF3-\xFF]~",
        self::ISO_8859_7 => "~[\x7F-\xFF\xAE\xD2\xFF]~",
        self::ISO_8859_8 => "~[\x7F-\xFF\xA1\xBF-\xDE\xFB\xFC\xFF]~",
        self::ISO_8859_9 => "~[\x7F-\xFF]~",
        self::ISO_8859_10 => "~[\x7F-\xFF]~",
        self::ISO_8859_11 => "~[\x7F-\xFF\xDB-\xDE\xFC-\xFF]~",
        self::ISO_8859_13 => "~[\x7F-\xFF]~",
        self::ISO_8859_14 => "~[\x7F-\xFF]~",
        self::ISO_8859_15 => "~[\x7F-\xFF]~",
        self::ISO_8859_16 => "~[\x7F-\xFF]~",
        self::WINDOWS_1250 => "~[\x81\x83\x88\x90\x98]~",
        self::WINDOWS_1251 => "~[\x98]~",
        self::WINDOWS_1252 => "~[\x81\x8D\x8F\x90\x9D]~",
        self::WINDOWS_1253 => "~[\x81\x88\x8A\x8C-\x8F\x90\x98\x9A\x9C-\x9F\xAA\xD2\xFF]~",
        self::WINDOWS_1254 => "~[\x81\x8D-\x8F\x90\x9D\x9E]~",
        self::WINDOWS_1255 => "~[\x81\x8A\x8C-\x8F\x90\x9A\x9C-\x9F\xD9-\xDE\xFB\xFC\xFF]~",
        self::WINDOWS_1256 => "~^(?!x)x~", // match nothing
        self::WINDOWS_1257 => "~[\x81\x83\x88\x8A\x8C\x90\x98\x9A\x9C\x9F\xA1\xA5]~",
        self::WINDOWS_1258 => "~[\x81\x8A\x8D-\x8F\x90\x9A\x9D\x9E]~",
        self::CP850 => "~^(?!x)x~", // match nothing
        self::CP852 => "~^(?!x)x~", // match nothing
        self::CP862 => "~^(?!x)x~", // match nothing
        self::CP866 => "~^(?!x)x~", // match nothing
    ];

    public static function validateValue(string &$value): bool
    {
        $value = self::normalize($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return 'BINARY|ASCII|UTF-(?:8|7(?:-IMAP)?|(?:(?:16|32)(?:BE|LE)?))|UCS-[24](?:BE|LE)'
            . '|ISO-8859-(?:1(0-6)?|[2-9])|WINDOWS-125[0-8]|CP[89]\\d\\d|CP5022[012]|CP51932'
            . '|EUC-(?:JP(?:-2004)?|CN|TW|KR)|EUC-JP-WIN|JIS(?:-MS)?|SJIS(?:-WIN|-MAC|2004)?'
            . '|ISO-2022-(?:JP(?:-MS|-2004)?|KR)|KOI8-[RUT]|GB18030|BIG-5|UHC|HZ|ARMSCII-8';
    }

    public static function normalize(string $encoding): string
    {
        $encoding = strtolower(str_replace(['-', '_'], '', $encoding));

        return self::$aliases[$encoding] ?? strtoupper($encoding);
    }

    public static function canCheck(string $encoding): bool
    {
        $encoding = self::normalize($encoding);

        return isset(self::$unused[$encoding]);
    }

    public static function check(string $value, string $encoding): bool
    {
        $encoding = self::normalize($encoding);

        $regexp = self::$unused[$encoding] ?? null;
        if ($regexp === null) {
            return false;
        }

        return preg_match($regexp, $value) === 0;
    }

}
