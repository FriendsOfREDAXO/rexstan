<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\ContentType;

use Dogma\Io\Path;
use Dogma\Language\Encoding;
use Dogma\StrictBehaviorMixin;
use finfo;
use function error_clear_last;
use function error_get_last;
use function finfo_buffer;
use function finfo_file;
use function finfo_open;
use const FILEINFO_MIME_ENCODING;
use const FILEINFO_MIME_TYPE;

class ContentTypeDetector
{
    use StrictBehaviorMixin;

    /** @var string|null */
    private $magicFile;

    /** @var resource|finfo|null */
    private $typeHandler;

    /** @var resource|finfo|null */
    private $encodingHandler;

    public function __construct(?string $magicFile = null)
    {
        $this->magicFile = $magicFile;
    }

    private function initTypeHandler(): void
    {
        error_clear_last();
        /** @var resource|false $typeHandler */
        $typeHandler = finfo_open(FILEINFO_MIME_TYPE, $this->magicFile);
        if ($typeHandler === false) {
            throw new ContentTypeDetectionException('Cannot initialize finfo extension: ' . error_get_last()['message']);
        }
        $this->typeHandler = $typeHandler;
    }

    private function initEncodingHandler(): void
    {
        error_clear_last();
        /** @var resource|false $encodingHandler */
        $encodingHandler = finfo_open(FILEINFO_MIME_ENCODING, $this->magicFile);
        if ($encodingHandler === false) {
            throw new ContentTypeDetectionException('Cannot initialize finfo extension. ' . error_get_last()['message']);
        }
        $this->encodingHandler = $encodingHandler;
    }

    /**
     * @param string|Path $file
     * @return ContentType|null
     */
    public function detectFileContentType($file): ?ContentType
    {
        if ($this->typeHandler === null) {
            $this->initTypeHandler();
        }

        error_clear_last();
        $path = $file instanceof Path ? $file->getPath() : $file;
        $type = finfo_file($this->typeHandler, $path);
        if ($type === false) {
            throw new ContentTypeDetectionException('Cannot read file info. ' . error_get_last()['message']);
        }

        return ContentType::get($type);
    }

    public function detectStringContentType(string $string): ?ContentType
    {
        if ($this->typeHandler === null) {
            $this->initTypeHandler();
        }

        error_clear_last();
        $type = finfo_buffer($this->typeHandler, $string);
        if ($type === false) {
            throw new ContentTypeDetectionException('Cannot read file info. ' . error_get_last()['message']);
        }

        return ContentType::get($type);
    }

    /**
     * @param string|Path $file
     * @return Encoding|null
     */
    public function detectFileEncoding($file): ?Encoding
    {
        if ($this->encodingHandler === null) {
            $this->initEncodingHandler();
        }

        error_clear_last();
        $path = $file instanceof Path ? $file->getPath() : $file;
        $type = finfo_file($this->encodingHandler, $path);
        if ($type === false) {
            throw new ContentTypeDetectionException('Cannot read file info. ' . error_get_last()['message']);
        }

        return Encoding::get($type);
    }

    public function detectStringEncoding(string $string): ?Encoding
    {
        if ($this->encodingHandler === null) {
            $this->initEncodingHandler();
        }

        error_clear_last();
        $type = finfo_buffer($this->encodingHandler, $string);
        if ($type === false) {
            throw new ContentTypeDetectionException('Cannot read file info. ' . error_get_last()['message']);
        }

        return Encoding::get($type);
    }

}
