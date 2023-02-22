<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_sql;
use function count;
use function in_array;

final class RexSqlDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['escape', 'escapelikewildcards', 'escapeidentifier'], true);
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

        $name = strtolower($methodReflection->getName());
        if ('escapeidentifier' === $name) {
            $identifierNames = $scope->getType($args[0]->value)->getConstantStrings();

            $result = [];
            foreach ($identifierNames as $identifierName) {
                // 1:1 copied rex_sql::escapeIdentifier()
                $escapedIdentifier = '`' . str_replace('`', '``', $identifierName->getValue()) . '`';
                $result[] = new ConstantStringType($escapedIdentifier);
            }

            if (count($result) >= 1) {
                return TypeCombinator::union(...$result);
            }
        }

        $argType = $scope->getType($args[0]->value);

        $intersection = [new StringType()];
        if ($argType->isNumericString()->yes()) {
            // a numeric string is by definition non-empty. therefore don't combine the 2 accessories
            $intersection[] = new AccessoryNumericStringType();
        } elseif ($argType->isNonEmptyString()->yes()) {
            $intersection[] = new AccessoryNonEmptyStringType();
        }

        if (count($intersection) > 1) {
            return new IntersectionType($intersection);
        }

        return new StringType();
    }
}
