<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
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
        if (1 < count($args)) {
            return [];
        }

        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }

        if (!RexSqlReflection::isSqlResultType($methodCall, $scope)) {
            return [];
        }

        if (RexSqlReflection::hasOffsetValueType($methodCall, $scope)) {
            return [];
        }

        $valueNameType = $scope->getType($args[0]->value);

        return [
            RuleErrorBuilder::message(
                sprintf('Value %s was not selected in the used sql-query.', $valueNameType->describe(VerbosityLevel::precise()))
            )->build(),
        ];
    }
}
