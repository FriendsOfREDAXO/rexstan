<?php declare(strict_types = 1);

namespace SqlFtw\Tests\Util;

use SqlFtw\Sql\Ddl\Table\Option\StorageEngine;
use SqlFtw\Sql\Expression\BaseType as T;
use SqlFtw\Sql\MysqlVariable;
use SqlFtw\Sql\VariableFlags as F;
use function is_float;
use function is_int;
use function is_string;
use function preg_match;

class MysqlVariableHelper
{

    /**
     * @return list<string>
     */
    public static function getSetVarEnabledVariables(): array
    {
        $enabled = [];
        foreach (MysqlVariable::getAllowedValues() as $variable) {
            $properties = MysqlVariable::$properties[$variable] ?? [];
            $flags = $properties[4] ?? 0;
            if (($flags & F::SET_VAR) !== 0) {
                $enabled[] = $variable;
            }
        }

        return $enabled;
    }

    public static function getSampleFormattedValue(string $variable): string
    {
        // default
        $value = MysqlVariable::$properties[$variable][3] ?? null;
        $var = MysqlVariable::getInfo($variable);

        if (is_string($value)) {
            if ($value === '') {
                if (($var->type === T::ENUM || $var->type === T::SET) && $var->values !== null) {
                    return (string) $var->values[0];
                }
                return "''";
            } elseif ($var->type === T::ENUM || $var->type === T::SET) {
                if (preg_match('~^[A-Za-z\d_]+$~', $value) !== 1) {
                    return "'{$value}'";
                }
            } elseif ($var->type === StorageEngine::class) {
                return $value;
            } else {
                return "'{$value}'";
            }
        } elseif (is_int($value)) {
            if ($value < $var->min) {
                $value = $var->min;
            }
            return (string) $value;
        } elseif (is_float($value)) {
            return (string) $value;
        } elseif ($value === false) {
            return 'FALSE';
        } elseif ($value === true) {
            return 'TRUE';
        } else { // null
            if (($var->type === T::ENUM || $var->type === T::SET) && $var->values !== null) {
                return (string) $var->values[0];
            }
            if ($var->nullable) {
                return 'NULL';
            } elseif ($var->type === T::CHAR) {
                return "'foo'";
            } else {
                return '0';
            }
        }

        return $value;
    }

}
