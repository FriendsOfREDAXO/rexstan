<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\Type;
use rex_sql;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use staabm\PHPStanDba\UnresolvableQueryException;
use function count;

final class RexSqlSetQueryTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;

    public function getClass(): string
    {
        return rex_sql::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
    {
        return 'setquery' === strtolower($methodReflection->getName());
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
    {
        // keep original param name because named-parameters
        $methodCall = $node;

        try {
            $inferedType = $this->inferStatementType($methodCall, $scope);
        } catch (UnresolvableQueryException $e) {
            return new SpecifiedTypes();
        }

        if (null !== $inferedType) {
            return $this->typeSpecifier->create($methodCall->var, $inferedType, TypeSpecifierContext::createTruthy(), true);
        }

        return new SpecifiedTypes();
    }

    /**
     * @throws UnresolvableQueryException
     */
    private function inferStatementType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (0 === count($args)) {
            return null;
        }

        if (1 === count($args)) {
            $queryExpr = $args[0]->value;
            $parameterTypes = null;
        } else {
            $queryExpr = $args[0]->value;
            $parameterTypes = $scope->getType($args[1]->value);
        }

        return RexSqlReflection::inferStatementType($queryExpr, $parameterTypes, $scope, QueryReflector::FETCH_TYPE_ASSOC);
    }
}
