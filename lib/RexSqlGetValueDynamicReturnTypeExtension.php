<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use rex_sql;
use function count;
use function in_array;

final class RexSqlGetValueDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['getvalue'], true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $args = $methodCall->getArgs();
        if (1 < count($args)) {
            return null;
        }

        $statementType = $scope->getType($methodCall->var);
        if (!$statementType instanceof GenericObjectType) {
            return null;
        }
        if (rex_sql::class !== $statementType->getClassName()) {
            return null;
        }

        $valueNameType = $scope->getType($args[0]->value);
        if (!$valueNameType instanceof ConstantStringType) {
            return null;
        }

        $sqlResultType = $statementType->getTypes()[0];
        if (!$sqlResultType instanceof ConstantArrayType) {
            return null;
        }

        if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
            return $sqlResultType->getOffsetValueType($valueNameType);
        }

        return null;
    }
}
