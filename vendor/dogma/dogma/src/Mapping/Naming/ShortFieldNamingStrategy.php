<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Naming;

use Dogma\StrictBehaviorMixin;
use function end;
use function explode;

class ShortFieldNamingStrategy implements NamingStrategy
{
    use StrictBehaviorMixin;

    /**
     * @param non-empty-string $fieldSeparator
     */
    public function translateName(string $localName, string $path, string $fieldSeparator): string
    {
        /** @var string[] $parts */
        $parts = explode($fieldSeparator, $localName);
        /** @var string $last */
        $last = end($parts);

        return $last;
    }

}
