<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
use rex_sql;
use function count;

/**
 * @implements Rule<MethodCall>
 */
final class RexSqlGetValueRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $methodCall, Scope $scope): array
    {
        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }

        $args = $methodCall->getArgs();
        if (1 < count($args)) {
            return [];
        }

        $varType = $scope->getType($methodCall->var);
        if (!$varType instanceof TypeWithClassName || $varType->getClassName() !== rex_sql::class) {
            return [];
        }

        $methodName = $methodCall->name->toString();
        if ('getvalue' !== strtolower($methodName)) {
            return [];
        }

        $statementType = $scope->getType($methodCall->var);
        if (!$statementType instanceof GenericObjectType) {
            return [];
        }
        if (rex_sql::class !== $statementType->getClassName()) {
            return [];
        }

        $valueNameType = $scope->getType($args[0]->value);
        if (!$valueNameType instanceof ConstantStringType) {
            return [];
        }

        $sqlResultType = $statementType->getTypes()[0];
        if (!$sqlResultType instanceof ConstantArrayType) {
            return [];
        }

        if ($sqlResultType->hasOffsetValueType($valueNameType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf('Value %s was not selected in the used sql-query.', $valueNameType->describe(VerbosityLevel::precise()))
            )->build(),
        ];
    }
}
