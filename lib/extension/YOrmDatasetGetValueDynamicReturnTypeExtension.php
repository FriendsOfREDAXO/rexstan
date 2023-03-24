<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_yform_manager_collection;
use rex_yform_manager_dataset;
use rex_yform_manager_query;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use function count;
use function in_array;

final class YOrmDatasetGetValueDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        // @phpstan-ignore-next-line
        return rex_yform_manager_dataset::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName())  === 'getvalue';
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

        if (!class_exists(rex_yform_manager_dataset::class)) {
            return null;
        }

        $key = $scope->getType($args[0]->value);
        $constantStrings = $key->getConstantStrings();
        if ($constantStrings === []) {
            return null;
        }

        $datasetObject = $scope->getType($methodCall->var);
        $classReflections = $datasetObject->getObjectClassReflections();

        $results = [];
        foreach($classReflections as $classReflection) {
            if (!$classReflection->isSubclassOf(rex_yform_manager_dataset::class)) {
                continue;
            }

            // @phpstan-ignore-next-line
            $datasetObject = call_user_func([$classReflection->getName(), 'create']);
            if (!$datasetObject instanceof rex_yform_manager_dataset) {
                throw new \RuntimeException('expecting dataset object');
            }

            foreach($constantStrings as $constantString) {
                $statementType = RexSqlReflection::inferStatementType(
                    new String_('SELECT * FROM '. $datasetObject->getTableName()),
                    null,
                    $scope,
                    QueryReflector::FETCH_TYPE_ASSOC
                );

                if ($statementType === null) {
                    continue;
                }

                $resultType = RexSqlReflection::getResultTypeFromStatementType($statementType);

                if ($resultType === null) {
                    continue;
                }

                if (!$resultType->hasOffsetValueType($constantString)->yes()) {
                    continue;
                }

                $results[] = $resultType->getOffsetValueType($constantString);
            }
        }

        if ($results === []) {
            return null;
        }

        return TypeCombinator::union(...$results);

    }
}
