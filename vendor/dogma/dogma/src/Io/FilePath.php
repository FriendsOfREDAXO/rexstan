<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\StrictBehaviorMixin;
use function basename;
use function str_replace;

class FilePath implements Path
{
    use StrictBehaviorMixin;

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = str_replace('\\', '/', $path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return basename($this->path);
    }

}
