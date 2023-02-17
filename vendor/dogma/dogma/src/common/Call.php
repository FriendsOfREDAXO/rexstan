<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Call
{
    use StaticClassMixin;

    /**
     * Call function with each given value as param
     *
     * @param callable $function ($value, $key)
     * @param iterable|mixed[] $values
     */
    public static function with(callable $function, iterable $values): void
    {
        foreach ($values as $i => $value) {
            $function($value, $i);
        }
    }

    /**
     * Call function with each set of given arguments
     *
     * @param callable $function (...$values, $key)
     * @param iterable|mixed[][] $arguments
     */
    public static function withArgs(callable $function, iterable $arguments): void
    {
        foreach ($arguments as $i => $args) {
            $args[] = $i;
            $function(...$args);
        }
    }

    /**
     * Call given function n times. Syntactic sugar for simple `for (...) {...}`
     *
     * @param callable $function (int $i)
     * @return mixed[]
     */
    public static function nTimes(callable $function, int $n): array
    {
        $results = [];
        for ($i = 0; $i < $n; $i++) {
            $results[] = $function($i);
        }

        return $results;
    }

}
