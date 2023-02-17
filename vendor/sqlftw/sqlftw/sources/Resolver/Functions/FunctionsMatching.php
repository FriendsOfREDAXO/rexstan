<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use Dogma\Str;
use SqlFtw\Sql\Expression\Value;
use function preg_match;
use function preg_replace;
use function soundex;
use function strpos;

trait FunctionsMatching
{

    /**
     * REGEXP - Whether string matches regular expression
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     */
    public function _regexp($string, $pattern): ?bool
    {
        $string = $this->cast->toString($string);
        $pattern = $this->cast->toString($pattern);

        if ($string === null || $pattern === null) {
            return null;
        } else {
            // todo: selecting safe delimiters
            return preg_match("~$pattern~i", $pattern) !== 0;
        }
    }

    /**
     * NOT REGEXP - Negation of REGEXP
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     */
    public function _not_regexp($string, $pattern): ?bool
    {
        return $this->_not($this->_regexp($string, $pattern));
    }

    /**
     * RLIKE - Whether string matches regular expression
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     */
    public function _rlike($string, $pattern): ?bool
    {
        return $this->_regexp($string, $pattern);
    }

    /**
     * SOUNDS LIKE - Compare sounds
     *
     * @param scalar|Value|null $left
     * @param scalar|Value|null $right
     */
    public function _sounds_like($left, $right): ?bool
    {
        $left = $this->cast->toString($left);
        $right = $this->cast->toString($right);

        if ($left === null || $right === null) {
            return null;
        } else {
            return soundex($left) === soundex($right);
        }
    }

    /**
     * REGEXP_INSTR() - Starting index of substring matching regular expression
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     * @param scalar|Value|null $position
     * @param scalar|Value|null $occurrence
     * @param scalar|Value|null $return
     * @param scalar|Value|null $modifiers
     */
    /*public function regexp_instr($string, $pattern, $position = 1, $occurrence = 1, $return = 0, $modifiers = 'i'): ?int
    {
        $string = $this->cast->toString($string);
        $pattern = $this->cast->toString($pattern);
        $position = $this->cast->toInt($position);
        $occurrence = $this->cast->toInt($occurrence);
        $return = $this->cast->toInt($return);
        $modifiers = $this->cast->toString($modifiers);

        if ($string === null || $pattern === null || $position === null || $occurrence === null || $return === null || $modifiers === null) {
            return null;
        }

        ///
    }*/

    /**
     * REGEXP_LIKE() - Whether string matches regular expression
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     * @param scalar|Value|null $modifiers
     */
    public function regexp_like($string, $pattern, $modifiers = 'i'): ?bool
    {
        $string = $this->cast->toString($string);
        $pattern = $this->cast->toString($pattern);
        $modifiers = $this->cast->toString($modifiers);

        if ($string === null || $pattern === null || $modifiers === null) {
            return null;
        } else {
            return preg_match($this->formatPattern($pattern, $modifiers), $string) !== 0;
        }
    }

    /**
     * REGEXP_REPLACE() - Replace substrings matching regular expression
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $pattern
     * @param scalar|Value|null $replace
     * @param scalar|Value|null $position
     * @param scalar|Value|null $occurrence
     * @param scalar|Value|null $modifiers
     */
    public function regexp_replace($string, $pattern, $replace, $position = 1, $occurrence = 1, $modifiers = 'i'): ?string
    {
        $string = $this->cast->toString($string);
        $pattern = $this->cast->toString($pattern);
        $replace = $this->cast->toString($replace);
        $position = $this->cast->toInt($position);
        $occurrence = $this->cast->toInt($occurrence);
        $modifiers = $this->cast->toString($modifiers);

        if ($string === null || $pattern === null || $replace === null) {
            return null;
        }

        // todo: occurrence ignored
        $pattern = $this->formatPattern($pattern, $modifiers);
        if ($position !== null && $position !== 1) {
            $prefix = Str::substring($string, $position - 1);

            return $prefix . preg_replace($pattern, $replace, $string);
        } else {
            return preg_replace($pattern, $replace, $string);
        }
    }

    /// REGEXP_SUBSTR() - Return substring matching regular expression

    private function formatPattern(string $pattern, ?string $modifiers): string
    {
        static $tr = [
            'c' => '', // case sensitive
            'i' => '', // PCRE_CASELESS (default if "c" is not specified)
            'm' => 'm', // PCRE_MULTILINE
            'n' => 's', // PCRE_DOTALL
            'u' => '', // only \n (ignored)
        ];

        $modifiers = $modifiers ?? '';

        // todo: detect from collation
        $ci = strpos('c', $modifiers) !== false;

        // always u (UTF-8)
        $modifiers = $ci . 'u' . Str::replaceKeys($modifiers, $tr);

        return "~$pattern~$modifiers";
    }

    /// MATCH() - Perform full-text search

    /**
     * SOUNDEX() - Return a soundex string
     *
     * @param scalar|Value|null $string
     */
    public function soundex($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return soundex($string);
        }
    }

}
