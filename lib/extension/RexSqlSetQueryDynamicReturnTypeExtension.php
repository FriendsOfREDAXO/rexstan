<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use rex_sql;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use staabm\PHPStanDba\UnresolvableQueryException;

use function count;

final class RexSqlSetQueryDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName()) === 'setquery';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        try {
            return $this->inferStatementType($methodCall, $scope);
        } catch (UnresolvableQueryException $e) {
            return null;
        }
    }

    /**
     * @throws UnresolvableQueryException
     */
    private function inferStatementType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (count($args) === 0) {
            return null;
        }

        if (count($args) === 1) {
            $queryExpr = $args[0]->value;
            $parameterTypes = null;
        } else {
            $queryExpr = $args[0]->value;
            $parameterTypes = $scope->getType($args[1]->value);
        }

        return RexSqlReflection::inferStatementType($queryExpr, $parameterTypes, $scope, QueryReflector::FETCH_TYPE_ASSOC);
    }
}
