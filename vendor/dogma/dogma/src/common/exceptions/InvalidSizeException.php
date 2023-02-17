<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Throwable;
use function implode;
use function is_array;

class InvalidSizeException extends Exception
{

    /**
     * @param string|Type $type
     * @param int|int[] $actualSize
     * @param int[]|string[] $allowedSizes
     */
    public function __construct($type, $actualSize, array $allowedSizes, ?Throwable $previous = null)
    {
        $type = ExceptionTypeFormatter::format($type);

        if (!$allowedSizes) {
            parent::__construct("Size parameter is not allowed on type $type.", $previous);
        } else {
            $sizes = implode(', ', $allowedSizes);
            if (is_array($actualSize)) {
                $actualSize = implode(',', $actualSize);
            }
            parent::__construct("Size $actualSize is not valid for type $type. Allowed sizes: $sizes.", $previous);
        }
    }

}
