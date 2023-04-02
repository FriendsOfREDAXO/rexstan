<?php

declare(strict_types=1);

namespace rexstan;

use PDO;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use rex_sql;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use staabm\PHPStanDba\UnresolvableQueryException;

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

        if (count($args) === 0) {
            return null;
        }

        if (count($args) === 1) {
            $queryExpr = $args[0]->value;
            $parameterTypes = null;
        } else {
            $queryExpr = $args[0]->value;
            $parameterTypes = $scope->getType($args[1]->value);
        }

        $isFetchKeyPair = false;
        $fetch = QueryReflector::FETCH_TYPE_ASSOC;
        if (count($args) >= 3) {
            $scalars = $scope->getType($args[2]->value)->getConstantScalarTypes();

            foreach ($scalars as $fetchType) {
                if ($fetchType->getValue() === PDO::FETCH_NUM) {
                    $fetch = QueryReflector::FETCH_TYPE_NUMERIC;
                }
                if ($fetchType->getValue() === PDO::FETCH_KEY_PAIR) {
                    $isFetchKeyPair = true;
                    $fetch = QueryReflector::FETCH_TYPE_NUMERIC;
                }
            }
        }

        try {
            $statementType = RexSqlReflection::inferStatementType($queryExpr, $parameterTypes, $scope, $fetch);
        } catch (UnresolvableQueryException $e) {
            return null;
        }

        if ($statementType === null) {
            return null;
        }

        $resultType = RexSqlReflection::getResultTypeFromStatementType($statementType);
        if ($resultType === null) {
            return null;
        }

        if ($isFetchKeyPair) {
            return new ArrayType(
                $resultType->getOffsetValueType(new ConstantIntegerType(0)),
                $resultType->getOffsetValueType(new ConstantIntegerType(1))
            );
        }

        return new ArrayType(new IntegerType(), $resultType);
    }
}
