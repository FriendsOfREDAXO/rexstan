<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

trait StrictBehaviorMixin
{

    /**
     * @deprecated Magic calls are forbidden
     * @param mixed $args
     * @throws UndefinedMethodException
     */
    public function __call(string $name, $args): void
    {
        throw new UndefinedMethodException(static::class, $name);
    }

    /**
     * @deprecated Magic static calls are forbidden
     * @param mixed $args
     * @throws UndefinedMethodException
     */
    public static function __callStatic(string $name, $args): void
    {
        throw new UndefinedMethodException(static::class, $name);
    }

    /**
     * @deprecated Magic property access is forbidden
     * @throws UndefinedPropertyException
     */
    public function __get(string $name): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

    /**
     * @deprecated Magic property access is forbidden
     * @param mixed $value
     * @throws UndefinedPropertyException
     */
    public function __set(string $name, $value): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

    /**
     * @deprecated Magic property access is forbidden
     * @throws UndefinedPropertyException
     */
    public function __isset(string $name): bool
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

    /**
     * @deprecated Magic property access is forbidden
     * @throws UndefinedPropertyException
     */
    public function __unset(string $name): void
    {
        throw new UndefinedPropertyException(static::class, $name);
    }

}
