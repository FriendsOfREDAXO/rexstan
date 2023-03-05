<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: xb

namespace Dogma\Io;

use Dogma\StaticClassMixin;

class FileMode
{
    use StaticClassMixin;

    // if not found: ERROR; keep content
    public const OPEN_READ = 'rb';
    public const OPEN_READ_WRITE = 'r+b';

    // if found: ERROR; no content
    public const CREATE_WRITE = 'xb';
    public const CREATE_READ_WRITE = 'x+b';

    // if not found: create; keep content
    public const CREATE_OR_OPEN_WRITE = 'cb';
    public const CREATE_OR_OPEN_READ_WRITE = 'c+b';

    // if not found: create; truncate content
    public const CREATE_OR_TRUNCATE_WRITE = 'wb';
    public const CREATE_OR_TRUNCATE_READ_WRITE = 'w+b';

    // if not found: create; keep content, point to end of file, don't accept new position
    public const CREATE_OR_APPEND_WRITE = 'ab';
    public const CREATE_OR_APPEND_READ_WRITE = 'a+b';

}
