<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function array_keys;
use function array_values;
use function count;
use function is_array;
use function is_callable;
use function is_object;
use function preg_grep;
use function preg_last_error;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function preg_replace;
use function preg_replace_callback;
use function preg_split;
use function strlen;
use const PREG_GREP_INVERT;
use const PREG_OFFSET_CAPTURE;
use const PREG_PATTERN_ORDER;
use const PREG_SET_ORDER;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;
use const PREG_SPLIT_OFFSET_CAPTURE;
use const PREG_UNMATCHED_AS_NULL;

/**
 * Strings manipulation with regular expressions
 */
class Re
{
    use StaticClassMixin;

    // flags
    public const PATTERN_ORDER = PREG_PATTERN_ORDER;
    public const SET_ORDER = PREG_SET_ORDER;

    public const CAPTURE_OFFSET = PREG_OFFSET_CAPTURE; // also PREG_SPLIT_OFFSET_CAPTURE
    public const CAPTURE_DELIMITER = PREG_SPLIT_DELIM_CAPTURE;

    public const FILTER_EMPTY = PREG_SPLIT_NO_EMPTY;
    public const UNMATCHED_AS_NULL = PREG_UNMATCHED_AS_NULL;

    public const INVERT = PREG_GREP_INVERT;

    // modifiers
    public const CASE_INSENSITIVE = 'i';
    public const MULTI_LINE = 'm';
    public const DOT_MATCHES_NEW_LINES = 's';
    public const IGNORE_WHITESPACE = 'x';
    public const TRY_TO_OPTIMIZE = 'S';
    public const UNGREEDY = 'U';
    public const UTF_8 = 'u';

    /**
     * @see Str::count()
     * @return int
     */
    public static function count(string $string, string $pattern): int
    {
        return count(self::matchAll($string, $pattern, PREG_SET_ORDER));
    }

    /**
     * @param int $flags PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE
     * @return string[]
     */
    public static function split(string $string, string $pattern, int $flags = 0): array
    {
        // fix PREG_OFFSET_CAPTURE to PREG_SPLIT_OFFSET_CAPTURE
        if ($flags & PREG_OFFSET_CAPTURE) {
            $flags = ($flags & ~PREG_OFFSET_CAPTURE) | PREG_SPLIT_OFFSET_CAPTURE;
        }

        $result = preg_split($pattern, $string, -1, $flags);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        }

        return $result;
    }

    public static function pos(string $string, string $pattern, int $offset = 0): ?int
    {
        if ($offset > strlen($string)) {
            return null;
        }

        $result = preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE, $offset);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        } elseif ($result === 0) {
            return null;
        }

        return $matches[0][1];
    }

    public static function hasMatch(string $string, string $pattern, int $offset = 0): bool
    {
        if ($offset > strlen($string)) {
            return false;
        }

        $result = preg_match($pattern, $string, $matches, $offset);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        } elseif ($result === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param int $flags PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL
     * @return string[]|null
     */
    public static function match(string $string, string $pattern, int $flags = 0, int $offset = 0): ?array
    {
        if ($offset > strlen($string)) {
            return null;
        }

        $result = preg_match($pattern, $string, $matches, $flags, $offset);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        } elseif ($result === 0) {
            return null;
        }

        return $matches;
    }

    /**
     * Same as match(), but only returns first subpattern or null
     *
     * @param int $flags PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL
     */
    public static function submatch(string $string, string $pattern, int $flags = 0, int $offset = 0): ?string
    {
        if ($offset > strlen($string)) {
            return null;
        }

        $result = preg_match($pattern, $string, $matches, $flags, $offset);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        } elseif ($result === 0) {
            return null;
        }

        return $matches ? $matches[1] : null;
    }

    /**
     * @param int $flags PREG_SET_ORDER|PREG_PATTERN_ORDER
     * @return string[][]
     */
    public static function matchAll(string $string, string $pattern, int $flags = 0, int $offset = 0): array
    {
        if ($offset > strlen($string)) {
            return [];
        }

        $result = preg_match_all($pattern, $string, $matches, $flags, $offset);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        } elseif ($result === 0) {
            return [];
        }

        return $matches;
    }

    /**
     * Same as matchAll(), but only returns first subpattern from each match
     *
     * @return string[]
     */
    public static function submatchAll(string $string, string $pattern, int $offset = 0): array
    {
        if ($offset > strlen($string)) {
            return [];
        }

        $result = preg_match_all($pattern, $string, $matches, PREG_PATTERN_ORDER, $offset);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        } elseif ($result === 0) {
            return [];
        }

        return $matches[1];
    }

    /**
     * @param string|string[] $pattern
     * @param string|callable|null $replacement
     * @return string
     */
    public static function replace(string $string, $pattern, $replacement = null, int $limit = -1): string
    {
        if (is_object($replacement) || is_array($replacement)) {
            if (!is_callable($replacement, false, $name)) {
                throw new InvalidArgumentException("Callback '$name' is not callable.");
            }

            $result = preg_replace_callback($pattern, $replacement, $string, $limit);
        } else {
            if ($replacement === null && is_array($pattern)) {
                $replacement = array_values($pattern);
                $pattern = array_keys($pattern);
            } else {
                /** @var string $replacement */
                $replacement = $replacement;
            }

            $result = preg_replace($pattern, $replacement, $string, $limit);
        }

        if ($result === null) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        }

        return $result;
    }

    /**
     * @param string[] $strings
     * @return string[]
     */
    public static function filter(array $strings, string $pattern, int $flags = 0): array
    {
        $result = preg_grep($pattern, $strings, $flags);
        if ($result === false) {
            $error = preg_last_error() ?: 0;

            throw new RegexpException($error);
        }

        return $result;
    }

    public static function quote(string $string, ?string $delimiter = null): string
    {
        return preg_quote($string, $delimiter);
    }

}
