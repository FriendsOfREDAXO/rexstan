<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_clang;

use function count;
use function in_array;

final class RexClangDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_clang::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['get', 'getstartid'], true);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $name = strtolower($methodReflection->getName());

        if ('getstartid' === $name) {
            return new ConstantIntegerType(rex_clang::getStartId());
        }

        if ('get' === $name) {
            $args = $methodCall->getArgs();
            if (count($args) < 1) {
                return null;
            }

            $type = $scope->getType($args[0]->value);
            $defaultReturn = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

            if ($type instanceof ConstantIntegerType) {
                $clang = rex_clang::get($type->getValue());
                if (null !== $clang) {
                    return TypeCombinator::removeNull($defaultReturn);
                }
            }
        }

        return null;
    }
}
