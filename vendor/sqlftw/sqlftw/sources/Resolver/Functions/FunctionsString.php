<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use Dogma\InvalidValueException;
use Dogma\Str;
use SqlFtw\Resolver\UnresolvableException;
use SqlFtw\Sql\Expression\Value;
use function array_reverse;
use function array_slice;
use function assert;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function ceil;
use function chunk_split;
use function count;
use function decbin;
use function dechex;
use function decoct;
use function explode;
use function hex2bin;
use function implode;
use function is_null;
use function is_string;
use function ltrim;
use function number_format;
use function ord;
use function preg_split;
use function rtrim;
use function str_repeat;
use function str_replace;
use function strlen;
use function strpos;
use function strrev;
use function substr;
use function trim;
use const PHP_INT_MAX;
use const PREG_SPLIT_NO_EMPTY;

trait FunctionsString
{

    /**
     * ASCII() - Return numeric value of left-most character
     *
     * @param scalar|Value|null $string
     */
    public function ascii($string): ?int
    {
        $string = $this->cast->toString($string);

        if (is_null($string)) {
            return null;
        } elseif ($string === '') {
            return 0;
        } else {
            return ord($string[0]);
        }
    }

    /**
     * BIN() - Return a string containing binary representation of a number
     *
     * @param scalar|Value|null $number
     */
    public function bin($number): ?string
    {
        $number = $this->cast->toInt($number);

        if (is_null($number)) {
            return null;
        } else {
            return decbin($number);
        }
    }

    /**
     * BIT_LENGTH() - Return length of argument in bits
     *
     * @param scalar|Value|null $string
     */
    public function bit_length($string): ?int
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return strlen($string) * 8;
        }
    }

    /**
     * CHAR() - Return the character for each integer passed
     *
     * @param scalar|Value|null $codes
     */
    public function char(...$codes): string
    {
        $chars = [];
        foreach ($codes as $code) {
            $code = $this->cast->toInt($code);
            if ($code === null) {
                continue;
            }
            $chars[] = Str::chr($code);
        }

        return implode('', $chars);
    }

    /**
     * CHAR_LENGTH() - Return number of characters in argument
     *
     * @param scalar|Value|null $string
     */
    public function char_length($string): ?int
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return Str::length($string);
        }
    }

    /**
     * CHARACTER_LENGTH() - Synonym for CHAR_LENGTH()
     *
     * @param scalar|Value|null $string
     */
    public function character_length($string): ?int
    {
        return $this->char_length($string);
    }

    /**
     * CONCAT() - Return concatenated string
     *
     * @param scalar|Value|null $strings
     */
    public function concat(...$strings): ?string
    {
        $str = [];
        foreach ($strings as $string) {
            $str[] = $string = $this->cast->toString($string);
            if ($string === null) {
                return null;
            }
        }

        return implode('', $str);
    }

    /**
     * CONCAT_WS() - Return concatenate with separator
     *
     * @param scalar|Value|null $separator
     * @param scalar|Value|null $strings
     */
    public function concat_ws($separator, ...$strings): ?string
    {
        $separator = $this->cast->toString($separator);

        if ($separator === null) {
            return null;
        }

        $str = [];
        foreach ($strings as $string) {
            $str[] = $string = $this->cast->toString($string);
            if ($string === null) {
                return null;
            }
        }

        return implode($separator, $str);
    }

    /**
     * ELT() - Return string at index number
     *
     * @param scalar|Value|null $index
     * @param scalar|Value|null $strings
     */
    public function elt($index, ...$strings): ?string
    {
        $index = $this->cast->toInt($index);

        if ($index === null || $index < 1 || $index > count($strings)) {
            return null;
        } else {
            return $this->cast->toString($strings[$index - 1]);
        }
    }

    /**
     * EXPORT_SET() - Return a string such that for every bit set in the value bits, you get an on string and for every unset bit, you get an off string
     *
     * @param scalar|Value|null $bits
     * @param scalar|Value|null $on
     * @param scalar|Value|null $off
     * @param scalar|Value|null $separator
     * @param scalar|Value|null $numberOfBits
     */
    public function export_set($bits, $on, $off, $separator = ',', $numberOfBits = 64): ?string
    {
        $bits = $this->cast->toInt($bits);
        $on = $this->cast->toString($on);
        $off = $this->cast->toString($off);
        $separator = $this->cast->toString($separator);
        $numberOfBits = $this->cast->toInt($numberOfBits);

        if ($bits === null || $on === null || $off === null || $separator === null || $numberOfBits === null) {
            return null;
        } elseif ($numberOfBits > 64 || $numberOfBits < 0) {
            $numberOfBits = 64;
        } elseif ($numberOfBits === 0) {
            return '';
        }

        $bits = strrev(decbin($bits)) . str_repeat('0', 64);
        $str = [];
        for ($n = 0; $n < $numberOfBits; $n++) {
            $str[] = $bits[$n] === '1' ? $on : $off;
        }

        return implode($separator, $str);
    }

    /**
     * FIELD() - Index (position) of first argument in subsequent arguments
     *
     * @param scalar|Value|null $find
     * @param scalar|Value|null $strings
     */
    public function field($find, ...$strings): int
    {
        $find = $this->cast->toString($find);

        if ($find === null) {
            return 0;
        }

        /** @var int|string $i */
        foreach ($strings as $i => $string) {
            $string = $this->cast->toString($string);
            if ($find === $string) {
                return ((int) $i) + 1;
            }
        }

        return 0;
    }

    /**
     * FIND_IN_SET() - Index (position) of first argument within second argument
     *
     * @param scalar|Value|null $find
     * @param scalar|Value|null $set
     */
    public function find_in_set($find, $set): ?int
    {
        $find = $this->cast->toString($find);
        $set = $this->cast->toString($set);

        if ($find === null || $set === null) {
            return null;
        }

        foreach (explode(',', $set) as $i => $string) {
            if ($find === $string) {
                return $i + 1;
            }
        }

        return 0;
    }

    /**
     * FORMAT() - Return a number formatted to specified number of decimal places
     *
     * @param scalar|Value|null $number
     * @param scalar|Value|null $decimals
     * @param scalar|Value|null $locale
     */
    public function format($number, $decimals, $locale = 'en_US'): ?string
    {
        $number = $this->cast->toFloat($number);
        $decimals = $this->cast->toInt($decimals);
        $locale = $this->cast->toString($locale);

        if ($number === null || $decimals === null) {
            return null;
        } else {
            // todo: locale ignored
            return number_format($number, $decimals, '.', ',');
        }
    }

    /**
     * FROM_BASE64() - Decode base64 encoded string and return result
     *
     * @param scalar|Value|null $string
     */
    public function from_base64($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        }

        $string = str_replace(["\n", "\r"], '', $string);
        if ((strlen($string) % 4) !== 0) {
            return null;
        } else {
            $result = base64_decode($string, true);

            return $result !== false ? $result : null;
        }
    }

    /**
     * HEX() - Hexadecimal representation of decimal or string value
     *
     * @param scalar|Value|null $value
     */
    public function hex($value): ?string
    {
        $value = $this->cast->toIntOrString($value);

        if ($value === null) {
            return null;
        } elseif (is_string($value)) {
            return bin2hex($value);
        } else {
            return dechex($value);
        }
    }

    /**
     * INSERT() - Insert substring at specified position up to specified number of characters
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $position
     * @param scalar|Value|null $length
     * @param scalar|Value|null $insert
     */
    public function insert($string, $position, $length, $insert): ?string
    {
        $string = $this->cast->toString($string);
        $position = $this->cast->toInt($position);
        $length = $this->cast->toInt($length);
        $insert = $this->cast->toString($insert);

        if ($string === null || $position === null || $length === null || $insert === null) {
            return null;
        } elseif ($position < 1 || $position > Str::length($string)) {
            return $string;
        } else {
            return Str::substring($string, 0, $position - 1) . $insert . Str::substring($string, $position + $length - 1);
        }
    }

    /**
     * INSTR() - Return the index of the first occurrence of substring
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $find
     */
    public function instr($string, $find): ?int
    {
        return $this->locate($find, $string);
    }

    /**
     * LCASE() - Synonym for LOWER()
     *
     * @param scalar|Value|null $string
     */
    public function lcase($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return Str::lower($string);
        }
    }

    /**
     * LEFT() - Return the leftmost number of characters as specified
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $length
     */
    public function left($string, $length): ?string
    {
        return $this->substring($string, 1, $length);
    }

    /**
     * LENGTH() - Return the length of a string in bytes
     *
     * @param scalar|Value|null $string
     */
    public function length($string): ?int
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return strlen($string);
        }
    }

    /**
     * LOAD_FILE() - Load the named file
     *
     * @param scalar|Value|null $file
     */
    public function load_file($file): ?string
    {
        $file = $this->cast->toString($file);

        if ($file === null) {
            return null;
        } else {
            throw new UnresolvableException('LOAD_FILE() is not implemented for security reasons.');
        }
    }

    /**
     * LOCATE() - Return the position of the first occurrence of substring
     *
     * @param scalar|Value|null $find
     * @param scalar|Value|null $string
     * @param scalar|Value|null $position
     */
    public function locate($find, $string, $position = 0): ?int
    {
        $find = $this->cast->toString($find);
        $string = $this->cast->toString($string);
        $position = $this->cast->toInt($position);

        if ($find === null || $string === null || $position === null) {
            return null;
        } else {
            $prefixLength = strlen(Str::substring($string, 0, $position));
            $suffix = substr($string, $prefixLength);
            $bytePosition = strpos($suffix, $find);
            if ($bytePosition === false) {
                return 0;
            }

            return Str::length(substr($suffix, 0, $bytePosition)) + $position + 1;
        }
    }

    /**
     * LOWER() - Return the argument in lowercase
     *
     * @param scalar|Value|null $string
     */
    public function lower($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return Str::lower($string);
        }
    }

    /**
     * LPAD() - Return the string argument, left-padded with the specified string
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $length
     * @param scalar|Value|null $pad
     */
    public function lpad($string, $length, $pad): ?string
    {
        $string = $this->cast->toString($string);
        $length = $this->cast->toInt($length);
        $pad = $this->cast->toString($pad);

        if ($string === null || $length === null || $pad === null) {
            return null;
        }

        $len = Str::length($string);
        if ($len === $length) {
            return $string;
        } elseif ($len > $length) {
            return Str::substring($string, 0, $length);
        } else {
            $padLength = $length - $len;
            $pad = str_repeat($pad, (int) ceil(($length - $len) / Str::length($pad)));

            return Str::substring($pad, 0, $padLength) . $string;
        }
    }

    /**
     * LTRIM() - Remove leading spaces
     *
     * @param scalar|Value|null $string
     */
    public function ltrim($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return ltrim($string, ' ');
        }
    }

    /**
     * MAKE_SET() - Return a set of comma-separated strings that have the corresponding bit in bits set
     *
     * @param scalar|Value|null $bits
     * @param scalar|Value|null $values
     */
    public function make_set($bits, ...$values): ?string
    {
        $bits = $this->cast->toInt($bits);

        if ($bits === null) {
            return null;
        }
        $result = [];
        /** @var int|string $i */
        foreach ($values as $i => $value) {
            if ($value !== null && ($bits & (2 ** (int) $i)) !== 0) {
                $result[] = $this->cast->toString($value);
            }
        }

        return implode(',', $result);
    }

    /**
     * MID() - Return a substring starting from the specified position
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $position
     * @param scalar|Value|null $length
     */
    public function mid($string, $position, $length = PHP_INT_MAX): ?string
    {
        return $this->substring($string, $position, $length);
    }

    /**
     * OCT() - Return a string containing octal representation of a number
     *
     * @param scalar|Value|null $value
     */
    public function oct($value): ?string
    {
        $value = $this->cast->toInt($value);

        if ($value === null) {
            return null;
        } else {
            return decoct($value);
        }
    }

    /**
     * OCTET_LENGTH() - Synonym for LENGTH()
     *
     * @param scalar|Value|null $string
     */
    public function octet_length($string): ?int
    {
        return $this->length($string);
    }

    /**
     * ORD() - Return character code for leftmost character of the argument
     *
     * @param scalar|Value|null $string
     */
    public function ord($string): ?int
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            try {
                return Str::ord($string);
            } catch (InvalidValueException $e) {
                return null;
            }
        }
    }

    /**
     * POSITION() - Synonym for LOCATE()
     *
     * @param scalar|Value|null $find
     * @param scalar|Value|null $string
     * @param scalar|Value|null $position
     */
    public function position($find, $string, $position = 0): ?int
    {
        return $this->locate($find, $string, $position);
    }

    /**
     * QUOTE() - Escape the argument for use in an SQL statement
     *
     * @param scalar|Value|null $string
     */
    public function quote($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return "'" . str_replace(['\\', '\'', "\x00", "\x1a"], ['\\\\', '\\\'', '\\0', '\\Z'], $string) . "'";
        }
    }

    /**
     * REPEAT() - Repeat a string the specified number of times
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $count
     */
    public function repeat($string, $count): ?string
    {
        $string = $this->cast->toString($string);
        $count = $this->cast->toInt($count);

        if ($string === null || $count === null) {
            return null;
        } else {
            return str_repeat($string, $count);
        }
    }

    /**
     * REPLACE() - Replace occurrences of a specified string
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $find
     * @param scalar|Value|null $replace
     */
    public function replace($string, $find, $replace): ?string
    {
        $string = $this->cast->toString($string);
        $find = $this->cast->toString($find);
        $replace = $this->cast->toString($replace);

        if ($string === null || $find === null || $replace === null) {
            return null;
        } else {
            return str_replace($find, $replace, $string);
        }
    }

    /**
     * REVERSE() - Reverse the characters in a string
     *
     * @param scalar|Value|null $string
     */
    public function reverse($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
            assert($chars !== false);

            return implode('', array_reverse($chars));
        }
    }

    /**
     * RIGHT() - Return the specified rightmost number of characters
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $length
     */
    public function right($string, $length): ?string
    {
        $string = $this->cast->toString($string);
        $length = $this->cast->toInt($length);

        if ($string === null || $length === null) {
            return null;
        } else {
            return Str::substring($string, -$length);
        }
    }

    /**
     * RPAD() - Append string the specified number of times
     * @param scalar|Value|null $string
     * @param scalar|Value|null $length
     * @param scalar|Value|null $pad
     */
    public function rpad($string, $length, $pad): ?string
    {
        $string = $this->cast->toString($string);
        $length = $this->cast->toInt($length);
        $pad = $this->cast->toString($pad);

        if ($string === null || $length === null || $pad === null) {
            return null;
        }

        $len = Str::length($string);
        if ($len === $length) {
            return $string;
        } elseif ($len > $length) {
            return Str::substring($string, 0, $length);
        } else {
            $padLength = $length - $len;
            $pad = str_repeat($pad, (int) ceil(($length - $len) / Str::length($pad)));

            return $string . Str::substring($pad, 0, $padLength);
        }
    }

    /**
     * RTRIM() - Remove trailing spaces
     *
     * @param scalar|Value|null $string
     */
    public function rtrim($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return rtrim($string, ' ');
        }
    }

    /**
     * SPACE() - Return a string of the specified number of spaces
     *
     * @param scalar|Value|null $count
     */
    public function space($count): ?string
    {
        $count = $this->cast->toInt($count);

        if ($count === null) {
            return null;
        } elseif ($count < 1) {
            return '';
        } else {
            return str_repeat(' ', $count);
        }
    }

    /**
     * SUBSTR() - Return the substring as specified
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $position
     * @param scalar|Value|null $length
     */
    public function substr($string, $position, $length = PHP_INT_MAX): ?string
    {
        return $this->substring($string, $position, $length);
    }

    /**
     * SUBSTRING() - Return the substring as specified
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $position
     * @param scalar|Value|null $length
     */
    public function substring($string, $position, $length = PHP_INT_MAX): ?string
    {
        $string = $this->cast->toString($string);
        $position = $this->cast->toInt($position);
        $length = $this->cast->toInt($length);

        if ($string === null || $position === null || $length === null) {
            return null;
        } else {
            return Str::substring($string, $position - 1, $length);
        }
    }

    /**
     * SUBSTRING_INDEX() - Return a substring from a string before the specified number of occurrences of the delimiter
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $delimiter
     * @param scalar|Value|null $count
     */
    public function substring_index($string, $delimiter, $count): ?string
    {
        $string = $this->cast->toString($string);
        $delimiter = $this->cast->toString($delimiter);
        $count = $this->cast->toInt($count);

        if ($string === null || $delimiter === null || $count === null) {
            return null;
        } elseif ($delimiter === '') {
            return '';
        } else {
            $parts = explode($delimiter, $string);
            $parts = $count >= 0
                ? array_slice($parts, 0, $count)
                : array_slice($parts, $count);

            return implode($delimiter, $parts);
        }
    }

    /**
     * TO_BASE64() - Return the argument converted to a base-64 string
     *
     * @param scalar|Value|null $string
     */
    public function to_base64($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return trim(chunk_split(base64_encode($string), 76, "\n"));
        }
    }

    /**
     * TRIM() - Remove leading and trailing spaces
     *
     * @param scalar|Value|null $string
     */
    public function trim($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return trim($string, ' ');
        }
    }

    /**
     * UCASE() - Synonym for UPPER()
     *
     * @param scalar|Value|null $string
     */
    public function ucase($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return Str::upper($string);
        }
    }

    /**
     * UNHEX() - Return a string containing hex representation of a number
     *
     * @param scalar|Value|null $hex
     */
    public function unhex($hex): ?string
    {
        $hex = $this->cast->toString($hex);
        if ($hex === null) {
            return null;
        } elseif ((strlen($hex) % 2) === 1) {
            $hex = '0' . $hex;
        }

        $string = hex2bin($hex);

        if ($string === false) {
            return null;
        } else {
            return $string;
        }
    }

    /**
     * UPPER() - Convert to uppercase
     *
     * @param scalar|Value|null $string
     */
    public function upper($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return Str::upper($string);
        }
    }

}
