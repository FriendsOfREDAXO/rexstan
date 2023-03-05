<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Util;

use Dogma\Re;
use function bin2hex;
use function chr;
use function count;
use function dechex;
use function hex2bin;
use function microtime;
use function ord;
use function random_int;
use function sprintf;
use function str_pad;
use function str_replace;
use function strlen;
use function substr;
use const PHP_INT_SIZE;
use const STR_PAD_LEFT;

class Uuid
{

    public static function v1_validate(string $uuid): bool
    {
        return Re::hasMatch('~^[0-9A-F]{8}-[0-9A-F]{4}-1[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$~i', $uuid)
            || Re::hasMatch('~^\{[0-9A-F]{8}-[0-9A-F]{4}-1[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}}$~i', $uuid)
            || Re::hasMatch('~^[0-9A-F]{12}1[0-9A-F]{3}[89AB][0-9A-F]{15}$~i', $uuid);
    }

    public static function v1_formatted_to_binary(string $formatted, bool $ordered = false): string
    {
        $formatted = str_replace('{-}', '', $formatted);

        /** @var string $bytes */
        $bytes = hex2bin($formatted);

        if ($ordered) {
            $bytes = $bytes[6] . $bytes[7] . $bytes[4] . $bytes[5]
                . $bytes[0] . $bytes[1] . $bytes[2] . $bytes[3]
                . substr($bytes, 8);
        }

        return $bytes;
    }

    public static function v1_binary_to_formatted(string $bytes, bool $ordered = false): string
    {
        if ($ordered) {
            $bytes = $bytes[4] . $bytes[5] . $bytes[6] . $bytes[7]
                . $bytes[2] . $bytes[3] . $bytes[0] . $bytes[1]
                . substr($bytes, 8);
        }

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
		);
    }

    // simplified version os symfony/polyfill-uuid
    public static function v1_generate(): string
    {
        // https://tools.ietf.org/html/rfc4122#section-4.1.4
        // 0x01b21dd213814000 is the number of 100-ns intervals between the
        // UUID epoch 1582-10-15 00:00:00 and the Unix epoch 1970-01-01 00:00:00.
        static $timeOffset = 0x01b21dd213814000;
        static $timeOffsetHex = "\x01\xb2\x1d\xd2\x13\x81\x40\x00";

        $time = microtime();
        $time = (int) (substr($time, 11) . substr($time, 2, 7));

        if (PHP_INT_SIZE >= 8) {
            $time = str_pad(dechex($time + $timeOffset), 16, '0', STR_PAD_LEFT);
        } else {
            $time = str_pad(self::toBinary((string) $time), 8, "\0", STR_PAD_LEFT);
            $time = self::binaryAdd($time, $timeOffsetHex);
            $time = bin2hex($time);
        }

        // https://tools.ietf.org/html/rfc4122#section-4.1.5
        // We are using a random data for the sake of simplicity: since we are
        // not able to get a super precise timeOfDay as a unique sequence
        $clockSeq = random_int(0, 0x3fff);

        static $node;
        if ($node === null) {
            $node = sprintf('%06x%06x', random_int(0, 0xffffff) | 0x010000, random_int(0, 0xffffff));
        }

        return sprintf(
            '%08s-%04s-1%03s-%04x-%012s',
            substr($time, -8), // 32 bits for "time_low"
            substr($time, -12, 4), // 16 bits for "time_mid"
            substr($time, -15, 3), // 16 bits for "time_hi_and_version", four most significant bits holds version number 1
            $clockSeq | 0x8000, // 16 bits: 8 bits for "clock_seq_hi_res", 8 bits for "clock_seq_low", two most significant bits holds zero and one for variant DCE1.1
            $node // 48 bits for "node"
        );
    }

    private static function toBinary(string $digits): string
    {
        $bytes = '';
        $count = strlen($digits);

        while ($count) {
            $quotient = [];
            $remainder = 0;

            for ($i = 0; $i !== $count; ++$i) {
                $carry = ((int) $digits[$i]) + $remainder * 10; // @phpstan-ignore-line Offset int<0, max> might not exist on array{}|array{int}|string.
                $digit = $carry >> 8;
                $remainder = $carry & 0xFF;

                if ($digit > 0 || $quotient !== []) {
                    $quotient[] = $digit;
                }
            }

            $bytes = chr($remainder) . $bytes;
            $count = count($digits = $quotient);
        }

        return $bytes;
    }

    private static function binaryAdd(string $a, string $b): string
    {
        $sum = 0;
        for ($i = 7; 0 <= $i; --$i) {
            $sum += ord($a[$i]) + ord($b[$i]);
            $a[$i] = chr($sum & 0xFF);
            $sum >>= 8;
        }

        return $a;
    }

}
