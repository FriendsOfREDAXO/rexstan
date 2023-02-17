<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use Dogma\Str;
use LogicException;
use SqlFtw\Resolver\UnresolvableException;
use SqlFtw\Sql\Expression\BaseType;
use SqlFtw\Sql\Expression\CastType;
use SqlFtw\Sql\Expression\Value;
use function is_int;
use function str_pad;
use function substr;

// @see: https://dev.mysql.com/doc/refman/8.0/en/cast-functions.html
trait FunctionsCast
{

    // BINARY - Cast a string to a binary string

    /**
     * CAST() - Cast a value as a certain type
     *
     * @param scalar|Value|null $value
     * @return int|float|string|null
     */
    public function cast($value, CastType $type)
    {
        $value = $this->cast->toIntOrString($value);
        if (is_int($value) && $value < 0 && $type->getSign() === false) {
            throw new UnresolvableException('Cannot represent integers over 63 bits.');
        }

        $baseType = $type->getBaseType();
        if ($baseType === null) {
            throw new UnresolvableException('Cannot cast.');
        }
        switch ($baseType->getValue()) {
            case BaseType::INTEGER:
            case BaseType::SIGNED:
            case BaseType::UNSIGNED:
                return $this->cast->toInt($value);
            case BaseType::DOUBLE:
                return $this->cast->toFloat($value);
            case BaseType::FLOAT:
                // todo: check precision
                return $this->cast->toFloat($value);
            case BaseType::REAL:
                // todo: REAL_AS_FLOAT
                return $this->cast->toFloat($value);
            case BaseType::DECIMAL:
                // todo: check precision ...
                return $this->cast->toFloat($value);
            case BaseType::CHAR:
            case BaseType::NCHAR:
                $string = $this->cast->toString($value);
                if ($string === null) {
                    return null;
                }
                $length = $type->getSize();
                if ($length !== null) {
                    $string = Str::substring($string, 0, $length[0]);
                }

                return $string;
            case BaseType::BINARY:
                $string = $this->cast->toString($value);
                if ($string === null) {
                    return null;
                }
                $length = $type->getSize();
                if ($length !== null) {
                    $string = substr($string, 0, $length[0]);
                    $string = str_pad($string, $length[0], "\0");
                }

                return $string;
            case BaseType::JSON:
            case BaseType::DATE:
            case BaseType::DATETIME:
            case BaseType::TIME:
            case BaseType::YEAR:
                throw new UnresolvableException('Casting to JSON, DATE, DATETIME, TIME, YEAR is not implemented.');
            default:
                throw new LogicException("Unknown type: {$baseType->getValue()}");
        }
    }

    // CONVERT() - Cast a value as a certain type

}
