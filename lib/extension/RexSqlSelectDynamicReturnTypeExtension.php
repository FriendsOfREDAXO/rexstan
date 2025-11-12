<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use rex_sql;
use staabm\PHPStanDba\UnresolvableQueryException;

final class RexSqlSelectDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName()) === 'select';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        try {
            return RexSqlSelectTypeSpecifyingExtension::inferStatementType($methodCall, $scope);
        } catch (UnresolvableQueryException $e) {
            return null;
        }
    }
}
