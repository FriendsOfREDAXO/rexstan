<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use rex_sql;

use function count;

final class RexSqlGetValueDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
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

        $offsetValueType = RexSqlReflection::getOffsetValueType($methodCall, $scope);
        if ($offsetValueType !== null) {
            return $offsetValueType;
        }

        return null;
    }
}
