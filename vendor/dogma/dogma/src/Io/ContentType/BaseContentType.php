<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\ContentType;

use Dogma\Enum\PartialStringEnum;

class BaseContentType extends PartialStringEnum
{

    public const APPLICATION = 'application';
    public const AUDIO = 'audio';
    public const FONT = 'font';
    public const CHEMICAL = 'chemical';
    public const IMAGE = 'image';
    public const MESSAGE = 'message';
    public const MODEL = 'model';
    public const MULTIPART = 'multipart';
    public const TEXT = 'text';
    public const VIDEO = 'video';

    public static function getValueRegexp(): string
    {
        return '[a-z_]+';
    }

}
