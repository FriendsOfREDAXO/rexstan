<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class LineEnding
{
    use StaticClassMixin;

    public const LF = "\n";
    public const CRLF = "\r\n";
    public const CR = "\r";

    public const LINUX = self::LF;
    public const WINDOWS = self::CRLF;
    public const MAC = self::CR;

}
