<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Throwable;

class FileException extends IoException
{

    /** @var mixed[]|null */
    private $error;

    /**
     * @param mixed[]|null $error
     */
    public function __construct(string $message, ?array $error = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->error = $error;
    }

    /**
     * @return mixed[]|null
     */
    public function getError(): ?array
    {
        return $this->error;
    }

}
