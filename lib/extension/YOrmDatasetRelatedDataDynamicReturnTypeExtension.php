<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\MethodCall;
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
use function count;
use function in_array;

final class YOrmDatasetRelatedDataDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        // @phpstan-ignore-next-line
        return rex_yform_manager_dataset::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(
            strtolower($methodReflection->getName()),
            ['getrelateddataset', 'getrelatedcollection', 'getrelatedquery'],
            true
        );
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

        if (
            !class_exists(rex_yform_manager_dataset::class)
            || !class_exists(rex_yform_manager_collection::class)
            || !class_exists(rex_yform_manager_query::class)
        ) {
            return null;
        }

        $key = $scope->getType($args[0]->value);
        $constantStrings = $key->getConstantStrings();
        if ($constantStrings === []) {
            return null;
        }

        $datasetObject = $scope->getType($methodCall->var);
        $classReflections = $datasetObject->getObjectClassReflections();
        $method = strtolower($methodReflection->getName());

        /** @var list<ObjectType> $results */
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
                $relation = $datasetObject->getTable()->getRelation($constantString->getValue());
                if ($relation === null) {
                    // unknown relation
                    continue;
                }
                $modelClass = rex_yform_manager_dataset::getModelClass($relation['table']);
                if ($modelClass === null) {
                    // not every table has a model class
                    continue;
                }

                $modelObjectType = new ObjectType($modelClass);
                if ($method === 'getrelateddataset') {
                    $results[] = $modelObjectType;
                } elseif ($method === 'getrelatedcollection') {
                    $results[] = new GenericObjectType(rex_yform_manager_collection::class, [$modelObjectType]);
                } elseif ($method !== 'getrelatedquery') {
                    $results[] = new GenericObjectType(rex_yform_manager_query::class, [$modelObjectType]);
                } else {
                    throw new \RuntimeException('Unknown method: '.$method);
                }
            }
        }

        if ($results === []) {
            return null;
        }

        return TypeCombinator::union(...$results);

    }
}
