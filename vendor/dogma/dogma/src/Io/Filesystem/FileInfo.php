<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Filesystem;

use Dogma\Io\File;
use Dogma\Io\FileException;
use Dogma\Io\FileMode;
use Dogma\Io\Path;
use Dogma\StrictBehaviorMixin;
use SplFileInfo;
use function basename;
use function str_replace;

/**
 * File Info
 *
 * inherits:
 * getATime()
 * getBasename()
 * getCTime()
 * getExtension()
 * getFileInfo()
 * getFilename()
 * getGroup()
 * getInode()
 * getLinkTarget()
 * getMTime()
 * getOwner()
 * getPath()
 * getPathInfo()
 * getPathname()
 * getPerms()
 * getRealPath()
 * getSize()
 * getType()
 * isDir()
 * isExecutable()
 * isFile()
 * isLink()
 * isReadable()
 * isWritable()
 * openFile() ***
 * setFileClass() ???
 * setInfoClass() ok
 * __toString() ???
 */
class FileInfo extends SplFileInfo implements Path
{
    use StrictBehaviorMixin;

    public function getPath(): string
    {
        return str_replace('\\', '/', parent::getPath());
    }

    public function getName(): string
    {
        return basename($this->getPath());
    }

    /**
     * @param resource|null $streamContext
     * @return File
     */
    public function open(string $mode = FileMode::OPEN_READ, $streamContext = null): File
    {
        $file = $this->getRealPath();
        if ($file === false) {
            throw new FileException('File does not exist.');
        }

        return new File($file, $mode, $streamContext);
    }

    /**
     * Is current or parent directory
     */
    public function isDot(): bool
    {
        return $this->getFilename() === '.' || $this->getFilename() === '..';
    }

}
