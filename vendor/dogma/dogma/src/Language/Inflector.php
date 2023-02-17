<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

use Dogma\Re;
use Dogma\StaticClassMixin;
use Dogma\Str;
use function array_key_exists;
use function array_values;
use function in_array;
use function preg_match;

class Inflector
{
    use StaticClassMixin;

    /** @var string[] */
    public static $singulars = [
        '/(quiz)$/i' => '\1zes',
        '/^(ox)$/i' => '\1en',
        '/([m|l])ouse$/i' => '\1ice',
        '/(matr|vert|ind)(?:ix|ex)$/i' => '\1ices',
        '/(x|ch|ss|sh)$/i' => '\1es',
        '/([^aeiouy]|qu)y$/i' => '\1ies',
        '/(hive)$/i' => '\1s',
        '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '\1a',
        '/(buffal|tomat)o$/i' => '\1oes',
        '/(bu)s$/i' => '\1ses',
        '/(alias|status)$/i' => '\1es',
        '/(octop|vir)us$/i' => '\1i',
        '/(ax|test)is$/i' => '\1es',
        '/s$/i' => 's',
        '/$/' => 's',
    ];

    /** @var string[] */
    public static $plurals = [
        '/(database)s$/i' => '\1',
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/(octop|vir)i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/s$/i' => '',
    ];

    /** @var string[] of irregular nouns */
    public static $irregular = [
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves',
        'cow' => 'kine',
    ];

    /** @var string[] of uncountable nouns */
    public static $uncountable = [
        'equipment',
        'information',
        'rice',
        'money',
        'species',
        'series',
        'fish',
        'sheep',
    ];

    public static function singularize(string $word): string
    {
        $lower = Str::lower($word);

        if (self::isSingular($word)) {
            return $word;
        }

        if (!self::isCountable($word)) {
            return $word;
        }

        if (self::isIrregular($word)) {
            foreach (self::$irregular as $single => $plural) {
                if ($lower === $plural) {
                    return $single;
                }
            }
        }

        foreach (self::$plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return Re::replace($word, $rule, $replacement);
            }
        }

        return $word;
    }

    public static function pluralize(string $word): string
    {
        $lower = Str::lower($word);

        if (self::isPlural($word)) {
            return $word;
        }

        if (!self::isCountable($word)) {
            return $word;
        }

        if (self::isIrregular($word)) {
            return self::$irregular[$lower];
        }

        foreach (self::$singulars as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return Re::replace($word, $rule, $replacement);
            }
        }

        return $word;
    }

    public static function isSingular(string $word): bool
    {
        if (!self::isCountable($word)) {
            return true;
        }

        return !self::isPlural($word);
    }

    public static function isPlural(string $word): bool
    {
        $lower = Str::lower($word);

        if (!self::isCountable($word)) {
            return true;
        }

        if (self::isIrregular($word)) {
            return in_array($lower, array_values(self::$irregular), true);
        }

        foreach (self::$plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return true;
            }
        }

        return false;
    }

    public static function isCountable(string $word): bool
    {
        $lower = Str::lower($word);

        return !in_array($lower, self::$uncountable, true);
    }

    public static function isIrregular(string $word): bool
    {
        $lower = Str::lower($word);

        return in_array($lower, self::$irregular, true) || array_key_exists($lower, self::$irregular);
    }

    /**
     * Ordinalize turns a number into an ordinal string used to denote
     * the position in an ordered sequence such as 1st, 2nd, 3rd, 4th.
     * @return string
     */
    public static function ordinalize(int $number): string
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            return $number . 'th';
        } else {
            switch ($number % 10) {
                case 1:
                    return $number . 'st';
                case 2:
                    return $number . 'nd';
                case 3:
                    return $number . 'rd';
                default:
                    return $number . 'th';
            }
        }
    }

}
