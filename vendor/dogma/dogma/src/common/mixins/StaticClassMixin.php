<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

trait StaticClassMixin
{

    /**
     * @throws StaticClassException
     */
    final public function __construct()
    {
        throw new StaticClassException(static::class);
    }

    /**
     * @deprecated Magic static call are forbidden
     * @param mixed $args
     * @throws UndefinedMethodException
     */
    public static function __callStatic(string $name, $args): void
    {
        throw new UndefinedMethodException(static::class, $name);
    }

}
