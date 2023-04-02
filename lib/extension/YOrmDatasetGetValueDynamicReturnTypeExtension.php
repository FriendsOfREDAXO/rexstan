<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_yform_manager_dataset;
use RuntimeException;

use function call_user_func;
use function count;

final class YOrmDatasetGetValueDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        // @phpstan-ignore-next-line
        return rex_yform_manager_dataset::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName()) === 'getvalue';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $args = $methodCall->getArgs();
        if (count($args) > 1) {
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
        foreach ($classReflections as $classReflection) {
            if (!$classReflection->isSubclassOf(rex_yform_manager_dataset::class)) {
                continue;
            }

            /** @phpstan-ignore-next-line */
            $datasetObject = call_user_func([$classReflection->getName(), 'create']);
            if (!$datasetObject instanceof rex_yform_manager_dataset) {
                throw new RuntimeException('expecting dataset object');
            }

            foreach ($constantStrings as $constantString) {
                $resultType = RexSqlReflection::getResultOffsetValueType(
                    'SELECT * FROM '. $datasetObject->getTableName(),
                    $constantString->getValue(),
                );

                if ($resultType === null) {
                    continue;
                }

                $results[] = $resultType;
            }
        }

        if ($results === []) {
            return null;
        }

        return TypeCombinator::union(...$results);
    }
}
