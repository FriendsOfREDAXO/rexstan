<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Error;
use ErrorException as PhpErrorException;
use Throwable;
use function is_array;

class ErrorException extends Exception
{

    /** @var string[]|int[]|null */
    private $error;

    /**
     * @param string[]|int[]|null $error
     */
    public function __construct(string $message, ?array $error, ?Throwable $previous = null)
    {
        if (is_array($error)) {
            $message .= ': ' . $error['type'] . ' "' . $error['message'] . '" in ' . $error['file'] . ':' . $error['line'];
            $this->error = $error;
        } elseif ($previous instanceof Error) {
            $message .= ': "' . $previous->getMessage() . '" in ' . $previous->getFile() . ':' . $previous->getLine();
            $this->error = [
                'type' => 'error',
                'message' => $previous->getMessage(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
            ];
        } elseif ($previous instanceof PhpErrorException) {
            $message .= ': ' . $previous->getSeverity() . ' "' . $previous->getMessage() . '" in ' . $previous->getFile() . ':' . $previous->getLine();
            $this->error = [
                'type' => $previous->getSeverity(),
                'message' => $previous->getMessage(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
            ];
        }

        parent::__construct($message, $previous);
    }

    /**
     * @return string[]|int[]|null
     */
    public function getErrorInfo(): ?array
    {
        return $this->error;
    }

}
