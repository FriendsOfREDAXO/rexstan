<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_sql;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use staabm\PHPStanDba\UnresolvableQueryException;
use function count;

final class RexSqlReflection
{
    public static function getSqlResultType(MethodCall $methodCall, Scope $scope): ?ConstantArrayType
    {
        $objectType = $scope->getType($methodCall->var);

        return self::getResultTypeFromStatementType($objectType);
    }

    public static function getOffsetValueType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if (count($args) < 1) {
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

    public static function getResultTypeFromStatementType(Type $statementType): ?ConstantArrayType
    {
        if (!$statementType instanceof GenericObjectType) {
            return null;
        }

        if (rex_sql::class !== $statementType->getClassName()) {
            return null;
        }

        $sqlResultType = $statementType->getTypes()[0];
        if (!$sqlResultType instanceof ConstantArrayType) {
            return null;
        }

        return $sqlResultType;
    }

    /**
     * @param QueryReflector::FETCH_* $fetchType
     * @throws UnresolvableQueryException
     */
    public static function inferStatementType(Expr $queryExpr, ?Type $parameterTypes, Scope $scope, int $fetchType): ?Type
    {
        if (null === $parameterTypes) {
            $queryReflection = new QueryReflection();
            $queryStrings = $queryReflection->resolveQueryStrings($queryExpr, $scope);
        } else {
            $queryReflection = new QueryReflection();
            $queryStrings = $queryReflection->resolvePreparedQueryStrings($queryExpr, $parameterTypes, $scope);
        }

        return self::createGenericObject($queryStrings, $fetchType);
    }

    /**
     * @param QueryReflector::FETCH_* $fetchType
     * @param iterable<string>            $queryStrings
     */
    private static function createGenericObject(iterable $queryStrings, int $fetchType): ?Type
    {
        $queryReflection = new QueryReflection();
        $genericObjects = [];

        foreach ($queryStrings as $queryString) {
            $resultType = $queryReflection->getResultType($queryString, $fetchType);

            if (null !== $resultType) {
                $genericObjects[] = new GenericObjectType(rex_sql::class, [$resultType]);
            }
        }

        if (count($genericObjects) > 1) {
            return TypeCombinator::union(...$genericObjects);
        }
        if (1 === count($genericObjects)) {
            return $genericObjects[0];
        }

        return null;
    }
}
