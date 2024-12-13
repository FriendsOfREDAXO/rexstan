<?php

declare(strict_types=1);

namespace rexstan;

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
use staabm\PHPStanDba\UnresolvableQueryException;

use function count;

final class RexSqlSetTableTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
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
        return strtolower($methodReflection->getName()) === 'settable';
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
            $inferedType = self::inferStatementType($methodCall, $scope);
        } catch (UnresolvableQueryException $e) {
            return new SpecifiedTypes();
        }

        if ($inferedType !== null) {
            return $this->typeSpecifier->create($methodCall->var, $inferedType, TypeSpecifierContext::createTruthy(), $scope)->setAlwaysOverwriteTypes();
        }

        return new SpecifiedTypes();
    }

    /**
     * @throws UnresolvableQueryException
     */
    public static function inferStatementType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (count($args) !== 1) {
            return null;
        }

        $tableNameType = $scope->getType($args[0]->value);
        if (count($tableNameType->getConstantStrings()) !== 1) {
            return null;
        }

        $calledOnType = $scope->getType($methodCall->var);
        if (!$calledOnType instanceof RexSqlGenericType && !$calledOnType instanceof RexSqlObjectType) {
            return null;
        }

        $calledOnType->setTableName($tableNameType->getConstantStrings()[0]->getValue());
        return $calledOnType;
    }
}
