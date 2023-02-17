<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Enum\StringEnum;

class HttpMethod extends StringEnum
{

    public const GET = 'get';
    public const HEAD = 'head';
    public const POST = 'post';
    public const PUT = 'put';
    public const PATCH = 'patch';
    public const DELETE = 'delete';
    public const TRACE = 'trace';
    public const OPTIONS = 'options';
    public const CONNECT = 'connect';

}
