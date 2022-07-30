<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use rex_sql;
use function count;

final class RexSqlReflection
{
    public static function isSqlResultType(MethodCall $methodCall, Scope $scope): bool
    {
        return null !== self::getSqlResultType($methodCall, $scope);
    }

    public static function getSqlResultType(MethodCall $methodCall, Scope $scope): ?ConstantArrayType
    {
        $type = $scope->getType($methodCall->var);

        if (!$type instanceof GenericObjectType) {
            return null;
        }

        if (rex_sql::class !== $type->getClassName()) {
            return null;
        }

        $sqlResultType = $type->getTypes()[0];
        if (!$sqlResultType instanceof ConstantArrayType) {
            return null;
        }

        return $sqlResultType;
    }

    public static function hasOffsetValueType(MethodCall $methodCall, Scope $scope): bool
    {
        return null !== self::getOffsetValueType($methodCall, $scope);
    }

    public static function getOffsetValueType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if (1 < count($args)) {
            return null;
        }

        $sqlResultType = self::getSqlResultType($methodCall, $scope);
        if (null === $sqlResultType) {
            return null;
        }

        $valueNameType = $scope->getType($args[0]->value);
        if (!$valueNameType instanceof ConstantStringType) {
            return null;
        }

        if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
            return $sqlResultType->getOffsetValueType($valueNameType);
        }

        // support table.field and db.table.field notation
        if (false !== strpos($valueNameType->getValue(), '.')) {
            $parts = explode('.', $valueNameType->getValue());
            $lastKey = array_key_last($parts);
            $fieldName = $parts[$lastKey];

            $valueNameType = new ConstantStringType($fieldName);
            if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
                return $sqlResultType->getOffsetValueType($valueNameType);
            }
        }

        return null;
    }
}
