<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use rex_sql;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use staabm\PHPStanDba\UnresolvableQueryException;
use function count;

final class RexSqlSetTableDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'settable' === strtolower($methodReflection->getName());
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?Type
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

        if (1 !== count($args)) {
            return null;
        }

        $tableNameType = $scope->getType($args[0]->value);
        if (count($tableNameType->getConstantStrings()) === 0) {
            return null;
        }

        $statementTypes = [];
        foreach($tableNameType->getConstantStrings() as $constantStringType) {
            $tableName = $constantStringType->getValue();
            $queryExpr = new String_('SELECT * FROM '.$tableName);

            $statementType = RexSqlReflection::inferStatementType($queryExpr, null, $scope, QueryReflector::FETCH_TYPE_ASSOC);
            if ($statementType === null) {
                return null;
            }

            $statementTypes[] = $statementType;
        }

        return TypeCombinator::union(...$statementTypes);
    }
}
