<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Classes.ParentCallSpacing.IncorrectLinesCountAfterControlStructure

namespace Dogma\Language;

use Dogma\StrictBehaviorMixin;
use Transliterator as PhpTransliterator;
use function array_map;
use function explode;
use function implode;
use function intl_get_error_message;
use function is_array;
use function is_string;

/**
 * @see http://userguide.icu-project.org/transforms/general
 */
class Transliterator extends PhpTransliterator
{
    use StrictBehaviorMixin;

    // general rules
    public const DECOMPOSE = 'Any-NFD';
    public const COMPOSE = 'Any-NFC';
    public const COMPATIBILITY_DECOMPOSE = 'Any-NFKD';
    public const COMPATIBILITY_COMPOSE = 'Any-NFKC';

    public const NULL = 'Any-Null';
    public const REMOVE = 'Any-Remove';

    public const LOWER_CASE = 'Any-Lower';
    public const UPPER_CASE = 'Any-Upper';
    public const TITLE_CASE = 'Any-Title';

    public const ENCODE_NAMES = 'Any-Name';
    public const DECODE_NAMES = 'Name-Any';

    public const HEX_C = 'Any-Hex/C';
    public const UNHEX_C = 'Hex-Any/C';
    public const HEX_JAVA = 'Any-Hex/Java';
    public const UNHEX_JAVA = 'Hex-Any/Java';
    public const HEX_PERL = 'Any-Hex/Perl';
    public const UNHEX_PERL = 'Hex-Any/Perl';
    public const HEX_XML = 'Any-Hex/XML';
    public const UNHEX_XML = 'Hex-Any/XML';

    public const FIX_ACCENTS = 'Any-Accents';
    public const ASCII_ACCENTS = 'Accents-Any';

    public const FIX_TYPOGRAPHY = 'Any-Publishing';
    public const ASCII_TYPOGRAPHY = 'Publishing-Any';

    public const TO_ASCII = 'Latin-ASCII';

    // script rules
    public const TO_ARABIC = 'Any-Arabic';
    public const TO_BENGALI = 'Any-Bengali';
    public const TO_CYRILLIC = 'Any-Cyrillic';
    public const TO_DEVANAGARI = 'Any-Devanagari';
    public const TO_GREEK = 'Any-Greek';
    public const TO_GREEK_UNGEGN = 'Any-Greek/UNGEGN';
    public const TO_GUJARATI = 'Any-Gujarati';
    public const TO_GURMUKHI = 'Any-Gurmukhi';
    public const TO_HAN = 'Any-Han';
    public const TO_HANGUL = 'Any-Hangul';
    public const TO_HEBREW = 'Any-Hebrew';
    public const TO_HIRAGANA = 'Any-Hiragana';
    public const TO_KANNADA = 'Any-Kannada';
    public const TO_KATAKANA = 'Any-Katakana';
    public const TO_LATIN = 'Any-Latin';
    public const TO_MALAYALAM = 'Any-Malayalam';
    public const TO_ORIYA = 'Any-Oriya';
    public const TO_TAMIL = 'Any-Tamil';
    public const TO_TELUGU = 'Any-Telugu';
    public const TO_THAI = 'Any-Thai'; // may not be available

    public const ARABIC_TO_LATIN = 'Arabic-Latin';
    public const BENGALI_TO_DEVANAGARI = 'Bengali-Devanagari';
    public const BENGALI_TO_GUJARATI = 'Bengali-Gujarati';
    public const BENGALI_TO_GURMUKHI = 'Bengali-Gurmukhi';
    public const BENGALI_TO_KANNADA = 'Bengali-Kannada';
    public const BENGALI_TO_LATIN = 'Bengali-Latin';
    public const BENGALI_TO_MALAYALAM = 'Bengali-Malayalam';
    public const BENGALI_TO_ORIYA = 'Bengali-Oriya';
    public const BENGALI_TO_TAMIL = 'Bengali-Tamil';
    public const BENGALI_TO_TELUGU = 'Bengali-Telugu';
    public const CYRILLIC_TO_LATIN = 'Cyrillic-Latin';
    public const DEVANAGARI_TO_BENGALI = 'Devanagari-Bengali';
    public const DEVANAGARI_TO_GUJARATI = 'Devanagari-Gujarati';
    public const DEVANAGARI_TO_GURMUKHI = 'Devanagari-Gurmukhi';
    public const DEVANAGARI_TO_KANNADA = 'Devanagari-Kannada';
    public const DEVANAGARI_TO_LATIN = 'Devanagari-Latin';
    public const DEVANAGARI_TO_MALAYALAM = 'Devanagari-Malayalam';
    public const DEVANAGARI_TO_ORIYA = 'Devanagari-Oriya';
    public const DEVANAGARI_TO_TAMIL = 'Devanagari-Tamil';
    public const DEVANAGARI_TO_TELUGU = 'Devanagari-Telugu';
    public const GREEK_TO_LATIN = 'Greek-Latin';
    public const GREEK_TO_LATIN_UNGEGN = 'Greek-Latin/UNGEGN';
    public const GUJARATI_TO_BENGALI = 'Gujarati-Bengali';
    public const GUJARATI_TO_DEVANAGARI = 'Gujarati-Devanagari';
    public const GUJARATI_TO_GURMUKHI = 'Gujarati-Gurmukhi';
    public const GUJARATI_TO_KANNADA = 'Gujarati-Kannada';
    public const GUJARATI_TO_LATIN = 'Gujarati-Latin';
    public const GUJARATI_TO_MALAYALAM = 'Gujarati-Malayalam';
    public const GUJARATI_TO_ORIYA = 'Gujarati-Oriya';
    public const GUJARATI_TO_TAMIL = 'Gujarati-Tamil';
    public const GUJARATI_TO_TELUGU = 'Gujarati-Telugu';
    public const GURMUKHI_TO_BENGALI = 'Gurmukhi-Bengali';
    public const GURMUKHI_TO_DEVANAGARI = 'Gurmukhi-Devanagari';
    public const GURMUKHI_TO_GUJARATI = 'Gurmukhi-Gujarati';
    public const GURMUKHI_TO_KANNADA = 'Gurmukhi-Kannada';
    public const GURMUKHI_TO_LATIN = 'Gurmukhi-Latin';
    public const GURMUKHI_TO_MALAYALAM = 'Gurmukhi-Malayalam';
    public const GURMUKHI_TO_ORIYA = 'Gurmukhi-Oriya';
    public const GURMUKHI_TO_TAMIL = 'Gurmukhi-Tamil';
    public const GURMUKHI_TO_TELUGU = 'Gurmukhi-Telugu';
    public const HAN_TO_LATIN = 'Han-Latin';
    public const HANGUL_TO_LATIN = 'Hangul-Latin';
    public const HEBREW_TO_LATIN = 'Hebrew-Latin';
    public const HIRAGANA_TO_KATAKANA = 'Hiragana-Katakana';
    public const HIRAGANA_TO_LATIN = 'Hiragana-Latin';
    public const JAMO_TO_LATIN = 'Jamo-Latin';
    public const KANNADA_TO_BENGALI = 'Kannada-Bengali';
    public const KANNADA_TO_DEVANAGARI = 'Kannada-Devanagari';
    public const KANNADA_TO_GUJARATI = 'Kannada-Gujarati';
    public const KANNADA_TO_GURMUKHI = 'Kannada-Gurmukhi';
    public const KANNADA_TO_LATIN = 'Kannada-Latin';
    public const KANNADA_TO_MALAYALAM = 'Kannada-Malayalam';
    public const KANNADA_TO_ORIYA = 'Kannada-Oriya';
    public const KANNADA_TO_TAMIL = 'Kannada-Tamil';
    public const KANNADA_TO_TELUGU = 'Kannada-Telugu';
    public const KATAKANA_TO_HIRAGANA = 'Katakana-Hiragana';
    public const KATAKANA_TO_LATIN = 'Katakana-Latin';
    public const LATIN_TO_ARABIC = 'Latin-Arabic';
    public const LATIN_TO_BENGALI = 'Latin-Bengali';
    public const LATIN_TO_CYRILLIC = 'Latin-Cyrillic';
    public const LATIN_TO_DEVANAGARI = 'Latin-Devanagari';
    public const LATIN_TO_GREEK = 'Latin-Greek';
    public const LATIN_TO_GREEK_UNGEON = 'Latin-Greek/UNGEGN';
    public const LATIN_TO_GUJARATI = 'Latin-Gujarati';
    public const LATIN_TO_GURMUKHI = 'Latin-Gurmukhi';
    public const LATIN_TO_HAN = 'Latin-Han';
    public const LATIN_TO_HANGUL = 'Latin-Hangul';
    public const LATIN_TO_HEBREW = 'Latin-Hebrew';
    public const LATIN_TO_HIRAGANA = 'Latin-Hiragana';
    public const LATIN_TO_JAMO = 'Latin-Jamo';
    public const LATIN_TO_KANNADA = 'Latin-Kannada';
    public const LATIN_TO_KATAKANA = 'Latin-Katakana';
    public const LATIN_TO_MALAYALAM = 'Latin-Malayalam';
    public const LATIN_TO_ORIYA = 'Latin-Oriya';
    public const LATIN_TO_TAMIL = 'Latin-Tamil';
    public const LATIN_TO_TELUGU = 'Latin-Telugu';
    public const LATIN_TO_THAI = 'Latin-Thai'; // may not be available
    public const MALAYALAM_TO_BENGALI = 'Malayalam-Bengali';
    public const MALAYALAM_TO_DEVANAGARI = 'Malayalam-Devanagari';
    public const MALAYALAM_TO_GUJARATI = 'Malayalam-Gujarati';
    public const MALAYALAM_TO_GURMUKHI = 'Malayalam-Gurmukhi';
    public const MALAYALAM_TO_KANNADA = 'Malayalam-Kannada';
    public const MALAYALAM_TO_LATIN = 'Malayalam-Latin';
    public const MALAYALAM_TO_ORIYA = 'Malayalam-Oriya';
    public const MALAYALAM_TO_TAMIL = 'Malayalam-Tamil';
    public const MALAYALAM_TO_TELUGU = 'Malayalam-Telugu';
    public const ORIYA_TO_BENGALI = 'Oriya-Bengali';
    public const ORIYA_TO_DEVANAGARI = 'Oriya-Devanagari';
    public const ORIYA_TO_GUJARATI = 'Oriya-Gujarati';
    public const ORIYA_TO_GURMUKHI = 'Oriya-Gurmukhi';
    public const ORIYA_TO_KANNADA = 'Oriya-Kannada';
    public const ORIYA_TO_LATIN = 'Oriya-Latin';
    public const ORIYA_TO_MALAYALAM = 'Oriya-Malayalam';
    public const ORIYA_TO_TAMIL = 'Oriya-Tamil';
    public const ORIYA_TO_TELUGU = 'Oriya-Telugu';
    public const TAMIL_TO_BENGALI = 'Tamil-Bengali';
    public const TAMIL_TO_DEVANAGARI = 'Tamil-Devanagari';
    public const TAMIL_TO_GUJARATI = 'Tamil-Gujarati';
    public const TAMIL_TO_GURMUKHI = 'Tamil-Gurmukhi';
    public const TAMIL_TO_KANNADA = 'Tamil-Kannada';
    public const TAMIL_TO_LATIN = 'Tamil-Latin';
    public const TAMIL_TO_MALAYALAM = 'Tamil-Malayalam';
    public const TAMIL_TO_ORIYA = 'Tamil-Oriya';
    public const TAMIL_TO_TELUGU = 'Tamil-Telugu';
    public const TELUGU_TO_BENGALI = 'Telugu-Bengali';
    public const TELUGU_TO_DEVANAGARI = 'Telugu-Devanagari';
    public const TELUGU_TO_GUJARATI = 'Telugu-Gujarati';
    public const TELUGU_TO_GURMUKHI = 'Telugu-Gurmukhi';
    public const TELUGU_TO_KANNADA = 'Telugu-Kannada';
    public const TELUGU_TO_LATIN = 'Telugu-Latin';
    public const TELUGU_TO_MALAYALAM = 'Telugu-Malayalam';
    public const TELUGU_TO_ORIYA = 'Telugu-Oriya';
    public const TELUGU_TO_TAMIL = 'Telugu-Tamil';
    public const THAI_TO_LATIN = 'Thai-Latin'; // may not be available

    public const FULLWIDTH_TO_HALFWIDTH = 'Fullwidth-Halfwidth';
    public const HALFWIDTH_TO_FULLWIDTH = 'Halfwidth-Fullwidth';

    /** @var PhpTransliterator[] */
    private static $instances = [];

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $id
     * @param int|null $direction
     * @return PhpTransliterator
     */
    public static function create($id, $direction = null): PhpTransliterator
    {
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        $transliterator = $direction !== null ? parent::create($id, $direction) : parent::create($id);
        if ($transliterator === null) {
            throw new TransliteratorException(intl_get_error_message());
        }
        self::$instances[$id] = $transliterator;

        return $transliterator;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param string $rules
     * @param int|null $direction
     * @return PhpTransliterator
     */
    public static function createFromRules($rules, $direction = null): PhpTransliterator
    {
        $transliterator = $direction !== null
            ? parent::createFromRules($rules, $direction)
            : parent::create($rules);

        if ($transliterator === null) {
            throw new TransliteratorException(intl_get_error_message());
        }

        return $transliterator;
    }

    /**
     * Accepts array of: rule id, tuple of rule id and filter or tuple of rule id and array of filters.
     * Example of transliterator for removing diacritics:
     * ```
     * Transliterator::createFromIds([
     *      Transliterator::DECOMPOSE,
     *      [Transliterator::REMOVE, CharacterCategory::NONSPACING_MARK],
     *      Transliterator::COMPOSE,
     * ]
     * ```
     * @see https://unicode-org.github.io/icu/userguide/transforms/general/rules.html
     *
     * @param non-empty-list<string|array{string, string|non-empty-list<string>}> $rules
     * @return PhpTransliterator
     */
    public static function createFromIds(array $rules, ?int $direction = null): PhpTransliterator
    {
        $rules = array_map(static function ($rule): string {
            if (is_string($rule)) {
                return $rule;
            }
            if (is_array($rule[1])) {
                return '[[:' . implode(':][:', $rule[1]) . ':]] ' . $rule[0];
            } else {
                return '[:' . $rule[1] . ':] ' . $rule[0];
            }
        }, $rules);

        return self::create(implode(';', $rules), $direction);
    }

    public static function createFromScripts(Script $fromScript, Script $toScript): PhpTransliterator
    {
        $from = explode(' ', $fromScript->getName())[0];
        $to = explode(' ', $toScript->getName())[0];

        return self::create("$from-$to");
    }

}
