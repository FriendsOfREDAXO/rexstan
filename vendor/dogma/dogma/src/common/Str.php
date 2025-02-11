<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Classes.ClassMemberSpacing.IncorrectCountOfBlankLinesBetweenMembers
// spell-check-ignore: joyofdata ci cj

namespace Dogma;

use Collator as PhpCollator;
use Dogma\Language\Collator;
use Dogma\Language\Locale\Locale;
use Dogma\Language\Transliterator;
use Dogma\Language\UnicodeCharacterCategory;
use Error;
use Nette\Utils\Strings;
use UConverter;
use function array_keys;
use function array_pop;
use function array_shift;
use function array_values;
use function chr;
use function class_exists;
use function count;
use function error_clear_last;
use function error_get_last;
use function function_exists;
use function iconv;
use function implode;
use function is_string;
use function mb_convert_case;
use function mb_convert_encoding;
use function mb_strlen;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function min;
use function ord;
use function range;
use function str_replace;
use function strcasecmp;
use function strcmp;
use function strlen;
use function strncmp;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;
use const MB_CASE_TITLE;

/**
 * UTF-8 strings manipulation
 */
class Str
{
    use StaticClassMixin;

    // proxy -----------------------------------------------------------------------------------------------------------

    public static function checkEncoding(string $string): bool
    {
        return $string === Strings::fixEncoding($string);
    }

    public static function fixEncoding(string $string): string
    {
        return Strings::fixEncoding($string);
    }

    public static function chr(int $code): string
    {
        if ($code < 0 || ($code >= 0xD800 && $code <= 0xDFFF) || $code > 0x10FFFF) {
            throw new InvalidValueException($code, 'unicode codepoint in range 0x00 to 0xD7FF or 0xE000 to 0x10FFFF');
        }

        if ($code <= 0x7F) {
            return chr($code); // 0-7,0-F
        } elseif ($code <= 0x7FF) {
            return chr(0b11000000 + ($code >> 6)) // 0xC-D,0-F,8-B,0-9
                . chr(128 + ($code & 63));
        } elseif ($code <= 0xFFFF) {
            return chr(0b11100000 + ($code >> 12)) // 0xE,0-F,8-B,0-9,8-B,0-9
                . chr(128 + (($code >> 6) & 63))
                . chr(128 + ($code & 63));
        } else {
            return chr(0b11110000 + ($code >> 18)) // 0xF,0-7,8-B,0-9,8-B,0-9,8-B,0-9
                . chr(128 + (($code >> 12) & 63))
                . chr(128 + (($code >> 6) & 63))
                . chr(128 + ($code & 63));
        }
    }

    public static function ord(string $ch): int
    {
        if ($ch === '') {
            throw new InvalidValueException($ch, "UTF-8 character");
        }

        $ord0 = ord($ch[0]);
        if ($ord0 <= 127) {
            return $ord0;
        } elseif ($ord0 >= 192 && $ord0 <= 223 && strlen($ch) >= 2) {
            $ord1 = ord($ch[1]);

            return ($ord0 - 192) * 64 + ($ord1 - 128);
        } elseif ($ord0 >= 224 && $ord0 <= 239 && strlen($ch) >= 3) {
            $ord1 = ord($ch[1]);
            $ord2 = ord($ch[2]);

            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        } elseif ($ord0 >= 240 && $ord0 <= 247 && strlen($ch) >= 4) {
            $ord1 = ord($ch[1]);
            $ord2 = ord($ch[2]);
            $ord3 = ord($ch[3]);

            return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
        } elseif ($ord0 >= 248 && $ord0 <= 251 && strlen($ch) >= 5) {
            $ord1 = ord($ch[1]);
            $ord2 = ord($ch[2]);
            $ord3 = ord($ch[3]);
            $ord4 = ord($ch[4]);

            return ($ord0 - 248) * 16777216 + ($ord1 - 128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
        } elseif ($ord0 >= 252 && $ord0 <= 253 && strlen($ch) >= 6) {
            $ord1 = ord($ch[1]);
            $ord2 = ord($ch[2]);
            $ord3 = ord($ch[3]);
            $ord4 = ord($ch[4]);
            $ord5 = ord($ch[5]);

            return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + ($ord5 - 128);
        }

        throw new InvalidValueException($ch, "UTF-8 character");
    }

    public static function startsWith(string $string, string $find): bool
    {
        return strncmp($string, $find, strlen($find)) === 0;
    }

    public static function endsWith(string $string, string $find): bool
    {
        return $find === '' || substr($string, -strlen($find)) === $find;
    }

    public static function contains(string $string, string $find): bool
    {
        return strpos($string, $find) !== false;
    }

    /**
     * @param string[] $find
     */
    public static function containsAny(string $string, array $find): bool
    {
        foreach ($find as $value) {
            if (strpos($string, $value) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $find
     */
    public static function containsAll(string $string, array $find): bool
    {
        foreach ($find as $value) {
            if (strpos($string, $value) === false) {
                return false;
            }
        }

        return true;
    }

    public static function substring(string $string, int $start, ?int $length = null): string
    {
        return Strings::substring($string, $start, $length);
    }

    public static function normalize(string $string): string
    {
        return Strings::normalize($string);
    }

    public static function normalizeNewLines(string $string): string
    {
        return str_replace(["\r\n", "\r"], "\n", $string);
    }

    public static function toAscii(string $string): string
    {
        return Strings::toAscii($string);
    }

    public static function webalize(string $string, ?string $chars = null, bool $lower = true): string
    {
        return Strings::webalize($string, $chars, $lower);
    }

    public static function truncate(string $string, int $maxLength, string $append = "\u{2026}"): string
    {
        return Strings::truncate($string, $maxLength, $append);
    }

    public static function indent(string $string, int $level = 1, string $chars = "\t"): string
    {
        return Strings::indent($string, $level, $chars);
    }

    public static function lower(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    public static function firstLower(string $string): string
    {
        return self::lower(self::substring($string, 0, 1)) . self::substring($string, 1);
    }

    public static function upper(string $string): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    public static function firstUpper(string $string): string
    {
        return self::upper(self::substring($string, 0, 1)) . self::substring($string, 1);
    }

    public static function capitalize(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    public static function length(string $string): int
    {
        return Strings::length($string);
    }

    public static function trim(string $string, string $chars = Strings::TRIM_CHARACTERS): string
    {
        return Strings::trim($string, $chars);
    }

    public static function padRight(string $string, int $length, string $pad = ' '): string
    {
        return Strings::padRight($string, $length, $pad);
    }

    public static function padLeft(string $string, int $length, string $pad = ' '): string
    {
        return Strings::padLeft($string, $length, $pad);
    }

    public static function before(string $string, string $find, int $nth = 1): ?string
    {
        return Strings::before($string, $find, $nth);
    }

    public static function after(string $string, string $find, int $nth = 1): ?string
    {
        return Strings::after($string, $find, $nth);
    }

    // substrings etc --------------------------------------------------------------------------------------------------

    public static function between(string $string, string $from, string $to): ?string
    {
        $after = self::after($string, $from);
        if ($after === null) {
            return null;
        }

        return self::before($after, $to);
    }

    /**
     * Similar to before(), but always returns start or the entire string
     *
     * @return string
     */
    public static function toFirst(string $string, string $search): string
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return $string;
        }

        return substr($string, 0, $pos);
    }

    /**
     * Similar to after(), but always returns end or entire string
     *
     * @return string
     */
    public static function fromFirst(string $string, string $search): string
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return '';
        }

        return substr($string, $pos + 1);
    }

    /**
     * @return string[]
     */
    public static function splitByFirst(string $string, string $search): array
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return [$string, ''];
        }

        return [substr($string, 0, $pos), substr($string, $pos + 1)];
    }

    /**
     * @return string[]
     */
    public static function splitByLast(string $string, string $search): array
    {
        $pos = strrpos($string, $search);
        if ($pos === false) {
            return [$string, ''];
        }

        return [substr($string, 0, $pos), substr($string, $pos + 1)];
    }

    /**
     * Position is per bytes, not per characters!
     */
    public static function getLineAt(string $string, int $bytePosition, string $separator = "\n"): string
    {
        if ($bytePosition >= strlen($string)) {
            return '';
        }

        $before = self::substring($string, 0, $bytePosition);
        $lineStart = strrpos($before, $separator);
        if ($lineStart === false) {
            $lineStart = -1;
        }
        $lineEnd = strpos($string, $separator, $bytePosition);
        if ($lineEnd === false) {
            $lineEnd = strlen($string);
        }

        return substr($string, $lineStart + 1, $lineEnd - $lineStart - 1);
    }

    /**
     * Implode with optional different separator for last item in the list ("A, B, C and D") and length limit ("A, B, C...")
     * @param array<string|int|float> $items
     */
    public static function join(
        array $items,
        string $separator = '',
        ?string $lastSeparator = null,
        ?int $maxLength = null,
        string $ellipsis = '…'
    ): string
    {
        if (count($items) === 0) {
            return '';
        } elseif (count($items) === 1) {
            return (string) array_pop($items);
        }

        if ($lastSeparator === null) {
            $result = implode($separator, $items);
        } else {
            $last = array_pop($items);

            $result = implode($separator, $items) . $lastSeparator . $last;
        }
        if ($maxLength === null || self::length($result) <= $maxLength) {
            return $result;
        }

        $maxLength -= self::length($ellipsis);
        $result = (string) array_shift($items);
        while (self::length($result) < $maxLength && $items !== []) {
            $result .= $separator . array_shift($items);
        }

        return $result . $ellipsis;
    }

    /**
     * @see Re::count()
     * @return int
     */
    public static function count(string $string, string $substring): int
    {
        return (strlen($string) - strlen(str_replace($substring, '', $string))) / strlen($substring);
    }

    /**
     * @deprecated use Str::count() instead
     * @return int
     */
    public static function substringCount(string $string, string $substring): int
    {
        return (strlen($string) - strlen(str_replace($substring, '', $string))) / strlen($substring);
    }

    /**
     * @param string[] $replacements
     */
    public static function replaceKeys(string $string, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    // misc ------------------------------------------------------------------------------------------------------------

    public static function underscore(string $string): string
    {
        return strtolower(Re::replace(
            Re::replace($string, '/([a-z\d])([A-Z])/', '\1_\2'),
            '/([A-Z]+)([A-Z])/',
            '\1_\2'
        ));
    }

    public static function trimLinesRight(string $string): string
    {
        return Re::replace($string, "/[\t ]+\n/", "\n");
    }

    // comparison ------------------------------------------------------------------------------------------------------

    /**
     * @param int|string|Collator|Locale $collation
     * @return bool
     */
    public static function equals(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): bool
    {
        return self::compare($first, $second, $collation) === 0;
    }

    /**
     * @param int|string|Collator|Locale $collation
     * @return int
     */
    public static function compare(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): int
    {
        if ($collation === CaseComparison::CASE_SENSITIVE) {
            return strcmp($first, $second);
        } elseif ($collation === CaseComparison::CASE_INSENSITIVE) {
            return strcasecmp($first, $second);
        } elseif (is_string($collation) || $collation instanceof Locale) {
            $collation = new Collator($collation);
        } elseif (!$collation instanceof PhpCollator) {
            throw new InvalidValueException($collation, [Type::STRING, PhpCollator::class, Locale::class]);
        }

        return $collation->compare($first, $second);
    }

    /**
     * Locates a "tag" surrounded by given markers which may be escaped. Returns start and length pair.
     *
     * E.g. called with ("foo {{no-tag}} {tag}}body} bar", '{', '}', '{', '}) will return [15, 12] for the "{tag{{body}"
     *
     * @return int[]|null[] ($start, $length)
     */
    public static function findTag(
        string $string,
        string $start,
        string $end,
        ?string $startEscape = null,
        ?string $endEscape = null,
        int $offset = 0
    ): ?array
    {
        $seDouble = $start === $startEscape;
        $seLength = $startEscape ? strlen($startEscape) : 0;
        do {
            $i = strpos($string, $start, $offset);
            if ($i === false) {
                return [null, null];
            }
            if ($startEscape === null) {
                break;
            }
            if ($seDouble) {
                $next = substr($string, $i + 1, $seLength);
                if ($next !== $startEscape) {
                    break;
                }
                $offset = $i + $seLength + 1;
            } else {
                $prev = substr($string, $i - $seLength, $seLength);
                if ($prev !== $startEscape) {
                    break;
                }
                $offset++;
            }
        } while (true);
        $offset = $i + strlen($start) + 1;

        $eeDouble = $end === $endEscape;
        $eeLength = $endEscape ? strlen($startEscape) : 0;
        do {
            $j = strpos($string, $end, $offset);
            if ($j === false) {
                return [null, null];
            }
            if ($endEscape === null) {
                break;
            }
            if ($eeDouble) {
                $next = substr($string, $j + 1, $eeLength);
                if ($next !== $endEscape) {
                    break;
                }
                $offset = $j + $eeLength + 1;
            } else {
                $prev = substr($string, $j - $eeLength, $eeLength);
                if ($prev !== $endEscape) {
                    break;
                }
                $offset++;
            }
        } while (true);

        return [$i, $j - $i + 1];
    }

    /**
     * Levenshtein distance for UTF-8 with additional weights for accent and case differences.
     * Expects input strings to be normalized UTF-8.
     *
     * @deprecated use levenshtein()
     */
    public static function levenshteinUnicode(
        string $string1,
        string $string2,
        float $insertionCost = 1.0,
        float $deletionCost = 1.0,
        float $replacementCost = 1.0,
        ?float $replacementAccentCost = 0.5,
        ?float $replacementCaseCost = 0.25
    ): float
    {
        if ($string1 === $string2) {
            return 0;
        }

        $length1 = mb_strlen($string1, 'UTF-8');
        $length2 = mb_strlen($string2, 'UTF-8');
        if ($length1 < $length2) {
            return self::levenshteinUnicode(
                $string2,
                $string1,
                $insertionCost,
                $deletionCost,
                $replacementCost,
                $replacementAccentCost,
                $replacementCaseCost
            );
        }
        if ($length1 === 0) {
            return (float) $length2;
        }

        $previousRow = range(0.0, $length2);
        for ($i = 0; $i < $length1; $i++) {
            $currentRow = [];
            $currentRow[0] = $i + 1.0;
            $char1 = mb_substr($string1, $i, 1, 'UTF-8');
            for ($j = 0; $j < $length2; $j++) {
                $char2 = mb_substr($string2, $j, 1, 'UTF-8');

                if ($char1 === $char2) {
                    $cost = 0;
                } elseif ($replacementCaseCost !== null && self::lower($char1) === self::lower($char2)) {
                    $cost = $replacementCaseCost;
                } elseif ($replacementAccentCost !== null && self::removeDiacritics($char1) === self::removeDiacritics($char2)) {
                    $cost = $replacementAccentCost;
                } elseif ($replacementCaseCost !== null && $replacementAccentCost !== null && self::removeDiacriticsAndLower($char1) === self::removeDiacriticsAndLower($char2)) {
                    $cost = $replacementCaseCost + $replacementAccentCost;
                } else {
                    $cost = $replacementCost;
                }
                $replacement = $previousRow[$j] + $cost;
                $insertions = $previousRow[$j + 1] + $insertionCost;
                $deletions = $currentRow[$j] + $deletionCost;

                $currentRow[] = min($replacement, $insertions, $deletions);
            }
            $previousRow = $currentRow;
        }

        return $previousRow[$length2];
    }

    /**
     * Levenshtein distance for UTF-8 with additional weights for accent and case differences.
     * Expects input strings to be normalized UTF-8.
     *
     * @see https://www.joyofdata.de/blog/comparison-of-string-distance-algorithms/
     */
    public static function levenshtein(
        string $string1,
        string $string2,
        int $insertCost = 4,
        int $deleteCost = 4,
        int $replaceCost = 4,
        ?int $accentCost = 2,
        ?int $caseCost = 1
    ): int
    {
        if ($string1 === $string2) {
            return 0;
        }

        $length1 = mb_strlen($string1, 'UTF-8');
        $length2 = mb_strlen($string2, 'UTF-8');
        if ($length1 < $length2) {
            return self::levenshtein(
                $string2,
                $string1,
                $insertCost,
                $deleteCost,
                $replaceCost,
                $accentCost,
                $caseCost
            );
        }
        if ($length1 === 0) {
            return $length2 * $insertCost;
        }

        $previousRow = range(0, $length2);
        for ($i = 0; $i < $length1; $i++) {
            $currentRow = [];
            $currentRow[0] = $i + 1;
            $ci = mb_substr($string1, $i, 1, 'UTF-8');
            for ($j = 0; $j < $length2; $j++) {
                $cj = mb_substr($string2, $j, 1, 'UTF-8');

                if ($ci === $cj) {
                    $cost = 0;
                } elseif ($caseCost !== null && self::lower($ci) === self::lower($cj)) {
                    $cost = $caseCost;
                } elseif ($accentCost !== null && self::removeDiacritics($ci) === self::removeDiacritics($cj)) {
                    $cost = $accentCost;
                } elseif ($caseCost !== null && $accentCost !== null && self::removeDiacriticsAndLower($ci) === self::removeDiacriticsAndLower($cj)) {
                    $cost = $caseCost + $accentCost;
                } else {
                    $cost = $replaceCost;
                }
                $replacement = $previousRow[$j] + $cost;
                $insertions = $previousRow[$j + 1] + $insertCost;
                $deletions = $currentRow[$j] + $deleteCost;

                $currentRow[] = min($replacement, $insertions, $deletions);
            }
            $previousRow = $currentRow;
        }

        return $previousRow[$length2];
    }

    /**
     * "Optimal string alignment distance" for Unicode (Levenshtein + swaps + one edit in one place only)
     * Expects input strings to be normalized UTF-8.
     */
    public static function optimalDistance(
        string $string1,
        string $string2,
        int $insertCost = 4,
        int $deleteCost = 4,
        int $swapCost = 4,
        int $replaceCost = 4,
        ?int $accentCost = 2,
        ?int $caseCost = 1
    ): int
    {
        $length1 = mb_strlen($string1, 'UTF-8');
        $length2 = mb_strlen($string2, 'UTF-8');
        if ($length1 === 0) {
            return $length2 * $insertCost;
        } elseif ($length2 === 0) {
            return $length1 * $deleteCost;
        }

        // for all $i and $j, $d[$i][$j] holds the string-alignment distance between the first $i characters of $s1 and the first $j characters of $s2.
        $d = [];
        for ($i = 0; $i <= $length1; $i++) {
            $d[$i] = [];
            $d[$i][0] = $i;
        }
        for ($j = 0; $j <= $length2; $j++) {
            $d[0][$j] = $j;
        }

        // determine substring distances
        for ($j = 0; $j < $length2; $j++) {
            $cj = mb_substr($string2, $j, 1, 'UTF-8');
            $cj1 = null;
            if ($j !== 0) {
                $cj1 = mb_substr($string2, $j - 1, 1, 'UTF-8');
            }
            for ($i = 0; $i < $length1; $i++) {
                $ci = mb_substr($string1, $i, 1, 'UTF-8');
                if ($ci === $cj) {
                    $cost = 0;
                } elseif ($caseCost !== null && self::lower($ci) === self::lower($cj)) {
                    $cost = $caseCost;
                } elseif ($accentCost !== null && self::removeDiacritics($ci) === self::removeDiacritics($cj)) {
                    $cost = $accentCost;
                } elseif ($caseCost !== null && $accentCost !== null && self::removeDiacriticsAndLower($ci) === self::removeDiacriticsAndLower($cj)) {
                    $cost = $caseCost + $accentCost;
                } else {
                    $cost = $replaceCost;
                }
                $d[$i + 1][$j + 1] = min($d[$i + 1][$j] + $insertCost, $d[$i][$j + 1] + $deleteCost, $d[$i][$j] + $cost);

                if ($i === 0 || $j === 0) {
                    continue;
                }
                $ci1 = mb_substr($string1, $i - 1, 1, 'UTF-8');
                if ($ci === $cj1 && $ci1 === $cj) {
                    $d[$i + 1][$j + 1] = min($d[$i + 1][$j + 1], $d[$i - 1][$j - 1] + ($ci === $cj ? 0 : $swapCost));
                }
            }
        }

        return $d[$length1][$length2];
    }

    /**
     * "Optimal string alignment distance" for binary data and ASCII (Levenshtein + swaps + one edit in one place only)
     */
    public static function optimalDistanceBin(
        string $string1,
        string $string2,
        int $insertCost = 1,
        int $deleteCost = 1,
        int $swapCost = 1,
        int $replaceCost = 1
    ): int
    {
        $length1 = strlen($string1);
        $length2 = strlen($string2);
        if ($length1 === 0) {
            return $length2 * $insertCost;
        } elseif ($length2 === 0) {
            return $length1 * $deleteCost;
        }

        // for all $i and $j, $d[$i][$j] holds the string-alignment distance between the first $i characters of $s1 and the first $j characters of $s2.
        $d = [];
        for ($i = 0; $i <= $length1; $i++) {
            $d[$i] = [];
            $d[$i][0] = $i;
        }
        for ($j = 0; $j <= $length2; $j++) {
            $d[0][$j] = $j;
        }

        // determine substring distances
        for ($j = 0; $j < $length2; $j++) {
            $cj = $string2[$j];
            $cj1 = null;
            if ($j !== 0) {
                $cj1 = $string2[$j - 1];
            }
            for ($i = 0; $i < $length1; $i++) {
                $ci = $string1[$i];
                $d[$i + 1][$j + 1] = min($d[$i + 1][$j] + $insertCost, $d[$i][$j + 1] + $deleteCost, $d[$i][$j] + ($ci === $cj ? 0 : $replaceCost));

                if ($i === 0 || $j === 0) {
                    continue;
                }
                $ci1 = $string1[$i - 1];
                if ($ci === $cj1 && $ci1 === $cj) {
                    $d[$i + 1][$j + 1] = min($d[$i + 1][$j + 1], $d[$i - 1][$j - 1] + ($ci === $cj ? 0 : $swapCost));
                }
            }
        }

        return $d[$length1][$length2];
    }

    // character manipulation ------------------------------------------------------------------------------------------

    public static function removeDiacritics(string $string): string
    {
        static $transliterator;
        if ($transliterator === null) {
            $transliterator = Transliterator::createFromIds([
                Transliterator::DECOMPOSE,
                [Transliterator::REMOVE, UnicodeCharacterCategory::NONSPACING_MARK],
                Transliterator::COMPOSE,
            ]);
        }

        return $transliterator->transliterate($string);
    }

    private static function removeDiacriticsAndLower(string $string): string
    {
        static $transliterator;
        if ($transliterator === null) {
            $transliterator = Transliterator::createFromIds([
                Transliterator::DECOMPOSE,
                [Transliterator::REMOVE, UnicodeCharacterCategory::NONSPACING_MARK],
                Transliterator::COMPOSE,
                Transliterator::LOWER_CASE,
            ]);
        }

        return $transliterator->transliterate($string);
    }

    public static function convertEncoding(string $string, string $from, string $to): string
    {
        if (function_exists('mb_convert_encoding')) {
            try {
                error_clear_last();
                $result = mb_convert_encoding($string, $to, $from);
                if (error_get_last() !== null) {
                    throw new ErrorException('Cannot convert encoding', error_get_last());
                }

                return $result;
            } catch (Error $e) {
                throw new ErrorException('Cannot convert encoding', null, $e);
            }
        } elseif (function_exists('iconv')) {
            try {
                error_clear_last();
                $result = iconv($from, $to, $string);
                if ($result === false) {
                    throw new ErrorException('Cannot convert encoding', error_get_last());
                }

                return $result;
            } catch (Error $e) {
                throw new ErrorException('Cannot convert encoding', null, $e);
            }
        } elseif (class_exists(UConverter::class)) {
            // from intl extension
            $converter = new UConverter($from, $to);

            error_clear_last();
            $result = $converter->convert($string);
            if ($result === false) {
                throw new ErrorException('Cannot convert encoding.', error_get_last());
            }

            return $result;
        } elseif (function_exists('recode_string')) {
            $request = $from . '..' . $to;
            try {
                error_clear_last();
                $result = recode_string($request, $string);
                if ($result === false) {
                    throw new ErrorException('Cannot convert encoding', error_get_last());
                }

                return $result;
            } catch (Error $e) {
                throw new ErrorException('Cannot convert encoding', null, $e);
            }
        } else {
            throw new ShouldNotHappenException('No extension for converting encodings installed.');
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @deprecated use Re::split() instead
     * @return string[]
     */
    public static function split(string $string, string $pattern, int $flags = 0): array
    {
        return Re::split($string, $pattern, $flags);
    }

    /**
     * @deprecated use Re::match() instead
     * @return string[]|null
     */
    public static function match(string $string, string $pattern, int $flags = 0, int $offset = 0): ?array
    {
        return Re::match($string, $pattern, $flags, $offset);
    }

    /**
     * @deprecated use Re::matchAll() instead
     * @return string[][]
     */
    public static function matchAll(string $string, string $pattern, int $flags = 0, int $offset = 0): array
    {
        return Re::matchAll($string, $pattern, $flags, $offset);
    }

    /**
     * @deprecated use Re::replace() instead
     * @param string|string[] $pattern
     * @param string|callable|null $replacement
     * @return string
     */
    public static function replace(string $string, $pattern, $replacement = null, int $limit = -1): string
    {
        return Re::replace($string, $pattern, $replacement, $limit);
    }

}
