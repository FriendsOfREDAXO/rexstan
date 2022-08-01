<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\VerbosityLevel;
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
        $args = $methodCall->getArgs();
        if (count($args) < 1) {
            return [];
        }

        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }

        if (!in_array(strtolower($methodCall->name->toString()), ['getvalue', 'getarrayvalue', 'getdatetimevalue'], true)) {
            return [];
        }

        if (null === RexSqlReflection::getSqlResultType($methodCall, $scope)) {
            return [];
        }

        $offsetValueType = RexSqlReflection::getOffsetValueType($methodCall, $scope);
        if (null !== $offsetValueType) {
            return [];
        }

        $valueNameType = $scope->getType($args[0]->value);
        if (!$valueNameType instanceof ConstantStringType) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf("Value '%s' was not selected in the used sql-query.", $valueNameType->getValue())
            )->build(),
        ];
    }
}
