<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_plugin;
use rex_yform_manager_dataset;
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

        if (!class_exists(rex_yform_manager_dataset::class)) {
            return null;
        }

        $key = $scope->getType($args[0]->value);
        $constantStrings = $key->getConstantStrings();
        if ($constantStrings === []) {
            return null;
        }

        $datasetObject = $scope->getType($methodCall->var);
        $objectClassNames = $datasetObject->getObjectClassNames();
        $method = strtolower($methodReflection->getName());

        /** @var list<ObjectType> $results */
        $results = [];
        foreach($objectClassNames as $objectClassName) {
            if (!is_a($objectClassName, rex_yform_manager_dataset::class, true)) {
                continue;
            }
            $datasetObject = call_user_func([$objectClassName, 'create']);

            foreach($constantStrings as $constantString) {
                if ($method === 'getrelateddataset') {
                    $relatedObject = $datasetObject->getRelatedDataset($constantString->getValue());
                } elseif ($method === 'getrelatedcollection') {
                    $relatedObject = $datasetObject->getRelatedDataset($constantString->getValue());
                } elseif ($method !== 'getrelatedquery') {
                    $relatedObject = $datasetObject->getRelatedQuery($constantString->getValue());
                } else {
                    throw new \RuntimeException('Unknown method: '.$method);
                }
                
                if ($relatedObject === null) {
                    continue;
                }
                if (!is_object($relatedObject)) {
                    throw new \RuntimeException('Expecting object, got '. gettype($relatedObject));
                }

                $results[] = new ObjectType(get_class($relatedObject));
            }
        }

        return TypeCombinator::union(...$results);

    }
}