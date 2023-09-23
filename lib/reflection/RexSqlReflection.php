<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
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
    public static function getSqlResultType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $objectType = $scope->getType($methodCall->var);

        if (
            in_array(strtolower($methodCall->name->toString()), ['setvalue', 'setarrayvalue'], true)
            && $objectType instanceof RexSqlObjectType
        ) {
            // don't take $sql->select() expression into account on $sql->setValue()
            // as this is likely used with e.g. $sql->update() which does not require a previously selected value
            $objectType->setSelectExpression('*');
        }

        return self::getResultTypeFromStatementType($objectType);
    }

    public static function getOffsetValueType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if (count($args) < 1) {
            return null;
        }

        $sqlResultType = self::getSqlResultType($methodCall, $scope);
        if ($sqlResultType === null) {
            return null;
        }

        $valueNameTypes = $scope->getType($args[0]->value)->getConstantStrings();

        $results = [];
        foreach ($valueNameTypes as $valueNameType) {
            if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
                $results[] = $sqlResultType->getOffsetValueType($valueNameType);
                continue;
            }

            // support table.field and db.table.field notation
            if (str_contains($valueNameType->getValue(), '.')) {
                $parts = explode('.', $valueNameType->getValue());
                $lastKey = array_key_last($parts);
                $fieldName = $parts[$lastKey];

                $valueNameType = new ConstantStringType($fieldName);
                if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
                    $results[] = $sqlResultType->getOffsetValueType($valueNameType);
                    continue;
                }
            }

            return null;
        }

        if (count($results) > 0) {
            return TypeCombinator::union(...$results);
        }

        return null;
    }

    public static function getResultTypeFromStatementType(Type $statementType): ?Type
    {
        if ($statementType instanceof RexSqlObjectType) {
            if ($statementType->getTableName() === null) {
                return null;
            }

            $colExpr = '*';
            if ($statementType->getSelectExpression() !== null) {
                $colExpr = $statementType->getSelectExpression();
            }

            $queryReflection = new QueryReflection();
            $resultType = $queryReflection->getResultType(
                'SELECT '. $colExpr .' FROM '.$statementType->getTableName(),
                QueryReflector::FETCH_TYPE_ASSOC
            );
            if ($resultType !== null) {
                return $resultType;
            }

            return null;
        }

        if (!$statementType instanceof GenericObjectType) {
            return null;
        }

        if ($statementType->getClassName() !== rex_sql::class) {
            return null;
        }

        $sqlResultType = $statementType->getTypes()[0];
        if (!$sqlResultType->isConstantArray()->yes()) {
            return null;
        }

        return $sqlResultType;
    }

    public static function getResultOffsetValueType(string $queryString, string $name): ?Type
    {
        $queryReflection = new QueryReflection();
        $resultType = $queryReflection->getResultType($queryString, QueryReflector::FETCH_TYPE_ASSOC);
        if ($resultType === null) {
            return null;
        }

        $offsetType = new ConstantStringType($name);
        if (!$resultType->hasOffsetValueType($offsetType)->yes()) {
            return null;
        }

        return $resultType->getOffsetValueType($offsetType);
    }

    /**
     * @param QueryReflector::FETCH_* $fetchType
     * @throws UnresolvableQueryException
     */
    public static function inferStatementType(Expr $queryExpr, ?Type $parameterTypes, Scope $scope, int $fetchType): ?Type
    {
        if ($parameterTypes === null) {
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

            if ($resultType !== null) {
                $genericObjects[] = new RexSqlGenericType($resultType);
            }
        }

        if (count($genericObjects) > 1) {
            return TypeCombinator::union(...$genericObjects);
        }
        if (count($genericObjects) === 1) {
            return $genericObjects[0];
        }

        return null;
    }
}
