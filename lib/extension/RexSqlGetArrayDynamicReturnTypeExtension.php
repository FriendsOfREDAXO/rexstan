<?php

declare(strict_types=1);

namespace rexstan;

use PDO;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantScalarType;
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

        $isFetchKeyPair = false;
        $fetch = QueryReflector::FETCH_TYPE_ASSOC;
        if (count($args) >= 3) {
            $fetchType = $scope->getType($args[2]->value);
            $scalars = $fetchType->getConstantScalarTypes();

            foreach($scalars as $fetchType) {
                if (PDO::FETCH_NUM === $fetchType->getValue()) {
                    $fetch = QueryReflector::FETCH_TYPE_NUMERIC;
                }
                if (PDO::FETCH_KEY_PAIR === $fetchType->getValue()) {
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

        if (null === $statementType) {
            return null;
        }

        $resultType = RexSqlReflection::getResultTypeFromStatementType($statementType);
        if (null === $resultType) {
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
