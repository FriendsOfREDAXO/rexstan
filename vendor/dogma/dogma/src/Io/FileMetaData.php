<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: ino nlinks uid gid rdev atime mtime ctime blksize

namespace Dogma\Io;

use Dogma\StrictBehaviorMixin;
use function error_clear_last;
use function error_get_last;
use function stat;

class FileMetaData
{
    use StrictBehaviorMixin;

    /** @var int[]|string[] */
    private $stat;

    /**
     * @param int[]|string[] $stat
     */
    public function __construct(array $stat)
    {
        $this->stat = $stat;
    }

    public static function get(string $path): self
    {
        error_clear_last();
        $stat = stat($path);

        if ($stat === false) {
            throw new FileException('Cannot acquire file metadata.', error_get_last());
        }

        return new self($stat);
    }

    public function getDeviceId(): int
    {
        return $this->stat['dev'];
    }

    public function getInode(): int
    {
        return $this->stat['ino'];
    }

    public function getPermissions(): int
    {
        return $this->stat['mode'];
    }

    public function getLinksCount(): int
    {
        return $this->stat['nlinks'];
    }

    public function getOwner(): int
    {
        return $this->stat['uid'];
    }

    public function getGroup(): int
    {
        return $this->stat['gid'];
    }

    public function getDeviceType(): string
    {
        return $this->stat['rdev'];
    }

    public function getSize(): int
    {
        return $this->stat['size'];
    }

    public function getAccessTime(): int
    {
        return $this->stat['atime'];
    }

    public function getModifyTime(): int
    {
        return $this->stat['mtime'];
    }

    public function getInodeChangeTime(): int
    {
        return $this->stat['ctime'];
    }

    public function getBlockSize(): int
    {
        return $this->stat['blksize'];
    }

    public function getBlocks(): int
    {
        return $this->stat['blocks'];
    }

}
