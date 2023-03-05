<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Formatter\Formatter;
use SqlFtw\Sql\Charset;
use function chr;
use function hexdec;
use function str_repeat;
use function strlen;
use function substr;

/**
 * e.g. 0x001F
 */
class HexadecimalLiteral implements BoolValue, StringValue, UintValue
{

    private string $value;

    private ?Charset $charset;

    public function __construct(string $value, ?Charset $charset = null)
    {
        $this->value = $value;
        $this->charset = $charset;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCharset(): ?Charset
    {
        return $this->charset;
    }

    public function asString(): string
    {
        $value = str_repeat('0', 2 - (strlen($this->value) % 2)) . $this->value;
        $string = '';
        $length = strlen($value);
        for ($n = 0; $n < $length; $n += 2) {
            $string .= chr((int) hexdec(substr($value, $n, 2)));
        }

        return $string;
    }

    public function asInt(): int
    {
        return (int) hexdec($this->value);
    }

    public function asBool(): ?bool
    {
        return ((int) hexdec($this->value)) !== 0;
    }

    public function serialize(Formatter $formatter): string
    {
        $result = '';
        if ($this->charset !== null) {
            $result .= '_' . $this->charset->serialize($formatter) . ' ';
        }

        return $this->value !== '' ? $result . '0x' . $this->value : "x''";
    }

}
