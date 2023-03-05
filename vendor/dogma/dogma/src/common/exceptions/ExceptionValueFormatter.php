<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function count;
use function get_class;
use function get_resource_type;
use function is_array;
use function is_bool;
use function is_object;
use function is_resource;
use function is_string;
use function md5;
use function serialize;
use function spl_object_hash;
use function sprintf;
use function strlen;
use function substr;

class ExceptionValueFormatter
{
    use StaticClassMixin;

    /**
     * @param mixed $value
     * @return string
     */
    public static function format($value): string
    {
        if (is_object($value)) {
            return sprintf('%s #%s', get_class($value), substr(md5(spl_object_hash($value)), 0, 8));
        } elseif (is_resource($value)) {
            return sprintf('resource (%s) #%d', get_resource_type($value), substr((string) $value, 13));
        } elseif (is_array($value)) {
            return sprintf('array (%d) #%s', count($value), substr(md5(serialize($value)), 0, 8));
        } elseif (is_string($value)) {
            return sprintf('"%s" (%d)', $value, strlen($value));
        } elseif (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } elseif ($value === null) {
            return 'NULL';
        } else { // integer, float
            return (string) $value;
        }
    }

}
