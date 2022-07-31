<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use rex_sql;
use function count;
use function in_array;

final class RexSqlGetArrayDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['getarray'], true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $args = $methodCall->getArgs();

        if (0 === count($args)) {
            return null;
        }

        if (1 === count($args)) {
            $queryExpr = $args[0]->value;
            $parameterTypes = null;
        } else {
            $queryExpr = $args[0]->value;
            $parameterTypes = $scope->getType($args[1]->value);
        }

        $statementType = RexSqlReflection::inferStatementType($queryExpr, $parameterTypes, $scope);
        if (null === $statementType) {
            return null;
        }

        $resultType = RexSqlReflection::getResultTypeFromStatementType($statementType);
        if (null === $resultType) {
            return null;
        }

        return new ArrayType(new IntegerType(), $resultType);
    }
}
