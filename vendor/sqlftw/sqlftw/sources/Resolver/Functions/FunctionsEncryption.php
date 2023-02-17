<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Resolver\UnresolvableException;
use SqlFtw\Sql\Expression\Value;
use function bzcompress;
use function bzdecompress;
use function crc32;
use function function_exists;
use function gzcompress;
use function gzuncompress;
use function hash;
use function is_string;
use function md5;
use function random_bytes;
use function sha1;
use function strlen;

trait FunctionsEncryption
{

    /// AES_DECRYPT() - Decrypt using AES
    /// AES_ENCRYPT() - Encrypt using AES

    /**
     * COMPRESS() - Return result as a binary string
     *
     * @param scalar|Value|null $string
     */
    public function compress($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        }
        if (function_exists('bzcompress')) {
            $result = bzcompress($string);
            if (is_string($result)) {
                return $result;
            }
        }
        if (function_exists('gzcompress')) {
            $result = gzcompress($string);
            if (is_string($result)) {
                return $result;
            }
        }

        return null;
    }

    /**
     * CRC32() - Compute a cyclic redundancy check value
     *
     * @param scalar|Value|null $string
     */
    public function crc32($string): ?int
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return crc32($string);
        }
    }

    /**
     * MD5() - Calculate MD5 checksum
     *
     * @param scalar|Value|null $string
     */
    public function md5($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return md5($string);
        }
    }

    /**
     * RANDOM_BYTES() - Return a random byte vector
     *
     * @param scalar|Value|null $length
     */
    public function random_bytes($length): ?string
    {
        $length = $this->cast->toInt($length);

        if ($length === null) {
            return null;
        } elseif ($length < 1) {
            throw new UnresolvableException('Invalid argument - length must be >= 1.');
        } else {
            return random_bytes($length);
        }
    }

    /**
     * SHA1(), SHA() - Calculate an SHA-1 160-bit checksum
     *
     * @param scalar|Value|null $string
     */
    public function sha($string): ?string
    {
        return $this->sha1($string);
    }

    /**
     * SHA1(), SHA() - Calculate an SHA-1 160-bit checksum
     *
     * @param scalar|Value|null $string
     */
    public function sha1($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        } else {
            return sha1($string);
        }
    }

    /**
     * SHA2() - Calculate an SHA-2 checksum
     *
     * @param scalar|Value|null $string
     * @param scalar|Value|null $length
     */
    public function sha2($string, $length): ?string
    {
        $string = $this->cast->toString($string);
        $length = $this->cast->toInt($length);

        if ($length === 0) {
            $length = 256;
        } elseif ($length !== 224 && $length !== 256 && $length !== 384 && $length !== 512) {
            return null;
        }

        if ($string === null) {
            return null;
        } else {
            return hash('sha' . $length, $string);
        }
    }

    /// STATEMENT_DIGEST() - Compute statement digest hash value
    /// STATEMENT_DIGEST_TEXT() - Compute normalized statement digest

    /**
     * UNCOMPRESS() - Uncompress a string compressed
     *
     * @param scalar|Value|null $string
     */
    public function uncompress($string): ?string
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        }
        if (function_exists('bzdecompress')) {
            $result = bzdecompress($string);
            if (is_string($result)) {
                return $result;
            }
        }
        if (function_exists('gzuncompress')) {
            $result = gzuncompress($string);
            if (is_string($result)) {
                return $result;
            }
        }

        return null;
    }

    /**
     * UNCOMPRESSED_LENGTH() - Return the length of a string before compression
     *
     * @param scalar|Value|null $string
     */
    public function uncompressed_length($string): ?int
    {
        $string = $this->cast->toString($string);

        if ($string === null) {
            return null;
        }
        if (function_exists('bzdecompress')) {
            $result = bzdecompress($string);
            if (is_string($result)) {
                return strlen($result);
            }
        }
        if (function_exists('gzuncompress')) {
            $result = gzuncompress($string);
            if (is_string($result)) {
                return strlen($result);
            }
        }

        return null;
    }

    /**
     * VALIDATE_PASSWORD_STRENGTH() - Determine strength of password
     *
     * @param scalar|Value|null $password
     */
    public function validate_password_strength($password): ?int
    {
        $password = $this->cast->toString($password);

        if ($password === null) {
            return null;
        } else {
            // pretend validation plugin is not installed
            return 0;
        }
    }

}
