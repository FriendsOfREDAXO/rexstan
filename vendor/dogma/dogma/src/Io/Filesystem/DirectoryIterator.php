<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Filesystem;

use FilesystemIterator;
use ReturnTypeWillChange;
use UnexpectedValueException;

class DirectoryIterator extends FilesystemIterator
{

    /** @var int|null */
    private $flags;

    public function __construct(string $path, ?int $flags = null)
    {
        if ($flags === null) {
            $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;
        }

        $this->flags = $flags;
        try {
            if (!($flags & FilesystemIterator::CURRENT_AS_PATHNAME) && !($flags & FilesystemIterator::CURRENT_AS_SELF)) {
                parent::__construct($path, $flags | FilesystemIterator::CURRENT_AS_PATHNAME);
            } else {
                parent::__construct($path, $flags);
            }
        } catch (UnexpectedValueException $e) {
            throw new DirectoryException($e->getMessage(), $e);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int|null $flags
     */
    public function setFlags($flags = null): void
    {
        $this->flags = $flags;
        if (!($flags & FilesystemIterator::CURRENT_AS_PATHNAME) && !($flags & FilesystemIterator::CURRENT_AS_SELF)) {
            parent::setFlags($flags | FilesystemIterator::CURRENT_AS_PATHNAME);
        } else {
            parent::setFlags($flags);
        }
    }

    /**
     * @return FileInfo|mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        if (!($this->flags & FilesystemIterator::CURRENT_AS_PATHNAME) && !($this->flags & FilesystemIterator::CURRENT_AS_SELF)) {
            /** @var string $path */
            $path = parent::current();

            return new FileInfo($path);
        }
        return parent::current();
    }

}
