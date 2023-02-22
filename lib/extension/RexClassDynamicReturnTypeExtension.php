<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex;
use rex_sql;
use function count;
use function in_array;

final class RexClassDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['gettable'], true);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $name = strtolower($methodReflection->getName());

        $args = $methodCall->getArgs();
        if (count($args) < 1) {
            return null;
        }

        if ('gettable' === $name) {
            $tableNames = $scope->getType($args[0]->value)->getConstantStrings();

            $result = [];
            foreach ($tableNames as $tableName) {
                $result[] = new ConstantStringType('rex_'. $tableName->getValue());
            }

            if (count($result) >= 1) {
                return TypeCombinator::union(...$result);
            }
        }

        return null;
    }
}
