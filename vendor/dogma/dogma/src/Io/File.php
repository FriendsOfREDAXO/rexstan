<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: maxmemory

namespace Dogma\Io;

use Dogma\InvalidArgumentException;
use Dogma\Io\Filesystem\FileInfo;
use Dogma\NonCloneableMixin;
use Dogma\NonSerializableMixin;
use Dogma\ResourceType;
use Dogma\StrictBehaviorMixin;
use const LOCK_UN;
use function basename;
use function chmod;
use function dirname;
use function error_clear_last;
use function error_get_last;
use function fclose;
use function feof;
use function fflush;
use function flock;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function ftruncate;
use function fwrite;
use function get_resource_type;
use function is_callable;
use function is_dir;
use function is_resource;
use function is_string;
use function min;
use function rename;
use function str_replace;
use function stream_get_meta_data;
use function strlen;
use function tmpfile;

/**
 * Binary file reader/writer
 */
class File implements Path
{
    use StrictBehaviorMixin;
    use NonCloneableMixin;
    use NonSerializableMixin;

    /** @var positive-int */
    public static $defaultChunkSize = 8192;

    /** @var string */
    protected $path;

    /** @var string */
    protected $mode;

    /** @var resource|null */
    protected $streamContext;

    /** @var resource|null */
    protected $handle;

    /** @var FileMetaData|null */
    private $metaData;

    /**
     * @param string|resource|FilePath|FileInfo $file
     * @param resource|null $streamContext
     */
    final public function __construct($file, string $mode = FileMode::OPEN_READ, $streamContext = null)
    {
        if (is_resource($file) && get_resource_type($file) === ResourceType::STREAM) {
            $this->handle = $file;
            $this->mode = $mode;
            return;
        } elseif (is_string($file)) {
            $this->path = $file;
        } elseif ($file instanceof FilePath || $file instanceof FileInfo) {
            $this->path = $file->getPath();
        } else {
            throw new InvalidArgumentException('Argument $file must be a file path or a stream resource.');
        }

        $this->mode = $mode;
        $this->streamContext = $streamContext;

        if ($this->handle === null) {
            $this->open();
        }
    }

    public function __destruct()
    {
        if ($this->handle !== null) {
            $this->close();
        }
    }

    public static function createTemporaryFile(): self
    {
        error_clear_last();
        /** @var resource|false $handle */
        $handle = tmpfile();

        if ($handle === false) {
            throw new FileException('Cannot create a temporary file.', error_get_last());
        }

        return new static($handle, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
    }

    public static function createMemoryFile(?int $maxSize = null): self
    {
        if ($maxSize === null) {
            return new static('php://memory', FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
        } else {
            return new static("php://temp/maxmemory:$maxSize", FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
        }
    }

    public function toTextFile(): TextFile
    {
        return new TextFile($this->handle, $this->mode, $this->streamContext);
    }

    /**
     * @return resource|null
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getPath(): string
    {
        if (empty($this->path)) {
            $meta = $this->getStreamMetaData();
            $this->path = str_replace('\\', '/', $meta['uri']);
        }

        return $this->path;
    }

    public function getName(): string
    {
        return basename($this->getPath());
    }

    public function move(string $path): self
    {
        $destination = dirname($path);
        if (!is_dir($destination)) {
            throw new FileException("Directory $destination is not writable.");
        }
        if (!rename($this->getPath(), $destination)) {
            throw new FileException("Cannot move file '{$this->getPath()}' to '$path'.");
        }
        chmod($destination, 0666);

        $this->path = $destination;

        return $this;
    }

    public function isOpen(): bool
    {
        return (bool) $this->handle;
    }

    public function open(): void
    {
        error_clear_last();
        if ($this->streamContext !== null) {
            $handle = fopen($this->path, $this->mode, false, $this->streamContext);
        } else {
            $handle = fopen($this->path, $this->mode, false);
        }
        if ($handle === false) {
            throw new FileException("Cannot open file in mode '$this->mode'.", error_get_last());
        }
        $this->handle = $handle;
    }

    public function close(): void
    {
        $this->checkOpened();

        error_clear_last();
        $result = fclose($this->handle);

        if ($result === false) {
            throw new FileException('Cannot close file.', error_get_last());
        }
        $this->metaData = null;
        $this->handle = null;
    }

    public function endOfFileReached(): bool
    {
        $this->checkOpened();

        error_clear_last();
        $feof = feof($this->handle);

        if ($feof === true) {
            $error = error_get_last();
            if ($error !== null) {
                throw new FileException('File interrupted.', $error);
            }
        }

        return $feof;
    }

    /**
     * @param positive-int|null $length
     */
    public function read(?int $length = null): string
    {
        $this->checkOpened();

        if ($length === null) {
            $length = self::$defaultChunkSize;
        }

        error_clear_last();
        $data = fread($this->handle, $length);

        if ($data === false) {
            if ($this->endOfFileReached()) {
                throw new FileException('Cannot read data from file. End of file was reached.', error_get_last());
            } else {
                throw new FileException('Cannot read data from file.', error_get_last());
            }
        }

        return $data;
    }

    /**
     * Copy range of data to another File or callback. Returns actual length of copied data.
     * @param File|callable $destination
     * @return int
     */
    public function copyData($destination, ?int $start = null, int $length = 0, ?int $chunkSize = null): int
    {
        if ($chunkSize === null) {
            $chunkSize = self::$defaultChunkSize;
        }
        if ($start !== null) {
            $this->setPosition($start);
        }

        $done = 0;
        /** @var positive-int $chunk */
        $chunk = $length ? min($length - $done, $chunkSize) : $chunkSize;
        while (!$this->endOfFileReached() && (!$length || $done < $length)) {
            $buff = $this->read($chunk);
            $done += strlen($buff);

            if ($destination instanceof self) {
                $destination->write($buff);

            } elseif (is_callable($destination)) {
                $destination($buff);

            } else {
                throw new InvalidArgumentException('Destination must be File or callable!');
            }
        }

        return $done;
    }

    public function getContent(): string
    {
        if ($this->getPosition()) {
            $this->setPosition(0);
        }

        $result = '';
        while (!$this->endOfFileReached()) {
            $result .= $this->read();
        }

        return $result;
    }

    public function write(string $data): void
    {
        $this->checkOpened();

        error_clear_last();
        $result = fwrite($this->handle, $data);

        if ($result === false) {
            throw new FileException('Cannot write data to file.', error_get_last());
        }
    }

    /**
     * Truncate file and move pointer at the end
     * @param int<0, max> $size
     */
    public function truncate(int $size = 0): void
    {
        $this->checkOpened();

        error_clear_last();
        $result = ftruncate($this->handle, $size);

        if ($result === false) {
            throw new FileException('Cannot truncate file.', error_get_last());
        }

        $this->setPosition($size);
    }

    /**
     * Flush the file output buffer (fsync)
     */
    public function flush(): void
    {
        $this->checkOpened();

        error_clear_last();
        $result = fflush($this->handle);

        if ($result === false) {
            throw new FileException('Cannot flush file cache.', error_get_last());
        }
        $this->metaData = null;
    }

    public function lock(?LockType $mode = null): void
    {
        $this->checkOpened();

        if ($mode === null) {
            $mode = LockType::get(LockType::SHARED);
        }

        error_clear_last();
        $wouldBlock = null;
        $result = flock($this->handle, $mode->getValue(), $wouldBlock);

        if ($result === false) {
            if ($wouldBlock) {
                throw new FileException('Non-blocking lock cannot be acquired.', error_get_last());
            } else {
                throw new FileException('Cannot lock file.', error_get_last());
            }
        }
    }

    public function unlock(): void
    {
        $this->checkOpened();

        error_clear_last();
        $result = flock($this->handle, LOCK_UN);

        if ($result === false) {
            throw new FileException('Cannot unlock file.', error_get_last());
        }
    }

    public function setPosition(int $position, ?FilePosition $from = null): void
    {
        $this->checkOpened();

        if ($position < 0) {
            $position *= -1;
            $from = FilePosition::get(FilePosition::END);
        }
        if ($from === null) {
            $from = FilePosition::get(FilePosition::BEGINNING);
        }

        error_clear_last();
        $result = fseek($this->handle, $position, $from->getValue());

        if ($result !== 0) {
            throw new FileException('Cannot set file pointer position.', error_get_last());
        }
    }

    public function getPosition(): int
    {
        $this->checkOpened();

        error_clear_last();
        $position = ftell($this->handle);

        if ($position === false) {
            throw new FileException('Cannot get file pointer position.', error_get_last());
        }

        return $position;
    }

    public function getMetaData(): FileMetaData
    {
        if (!$this->metaData) {
            if ($this->handle) {
                error_clear_last();
                $stat = fstat($this->handle);
                if (!$stat) {
                    throw new FileException('Cannot acquire file metadata.', error_get_last());
                }
                $this->metaData = new FileMetaData($stat);
            } else {
                if (empty($this->path)) {
                    $this->getPath();
                }
                $this->metaData = FileMetaData::get($this->path);
            }
        }

        return $this->metaData;
    }

    /**
     * Get stream meta data for files opened via HTTP, FTP…
     * @return mixed[]
     */
    public function getStreamMetaData(): array
    {
        return stream_get_meta_data($this->handle);
    }

    /**
     * Get stream wrapper headers (HTTP)
     * @return mixed[]
     */
    public function getHeaders(): array
    {
        $data = stream_get_meta_data($this->handle);

        return $data['wrapper_data'];
    }

    /*
    [
        [wrapper_data] => [
            [0] => HTTP/1.1 200 OK
            [1] => Server: Apache/2.2.3 (Red Hat)
            [2] => Last-Modified: Tue, 15 Nov 2005 13:24:10 GMT
            [3] => ETag: "b300b4-1b6-4059a80bfd280"
            [4] => Accept-Ranges: bytes
            [5] => Content-Type: text/html; charset=UTF-8
            [6] => Set-Cookie: FOO=BAR; expires=Fri, 21-Dec-2012 12:00:00 GMT; path=/; domain=.example.com
            [6] => Connection: close
            [7] => Date: Fri, 16 Oct 2009 12:00:00 GMT
            [8] => Age: 1164
            [9] => Content-Length: 438
        ]
        [wrapper_type] => http
        [stream_type] => tcp_socket/ssl
        [mode] => r
        [unread_bytes] => 438
        [seekable] =>
        [uri] => http://www.example.com/
        [timed_out] =>
        [blocked] => 1
        [eof] =>
    ]
    */

    private function checkOpened(): void
    {
        if ($this->handle === null) {
            throw new FileException('File is already closed.');
        }
    }

}
