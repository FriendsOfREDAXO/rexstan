<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\StringType;
use PHPStan\Type\VerbosityLevel;

use function count;
use function in_array;

/**
 * @implements Rule<MethodCall>
 */
final class RexSqlSetValueRule implements Rule
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

        if (!in_array(strtolower($methodCall->name->toString()), ['setvalue', 'setarrayvalue'], true)) {
            return [];
        }

        if (null === RexSqlReflection::getSqlResultType($methodCall, $scope)) {
            return [];
        }

        $offsetValueType = RexSqlReflection::getOffsetValueType($methodCall, $scope);
        if (null !== $offsetValueType) {
            if ('setarrayvalue' === strtolower($methodCall->name->toString()) && !$offsetValueType->accepts(new StringType(), false)->yes()) {
                return [
                    RuleErrorBuilder::message(
                        'setArrayValue() expects a database column which can store string values.'
                    )->build(),
                ];
            }

            return [];
        }

        $valueNameType = $scope->getType($args[0]->value);
        $strings = $valueNameType->getConstantStrings();

        if (1 === count($strings)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Value %s does not exist in table selected via setTable().', $valueNameType->describe(VerbosityLevel::precise()))
                )->build(),
            ];
        }

        return [
            RuleErrorBuilder::message(
                sprintf('All or one of the values %s was not selected in the used sql-query.', $valueNameType->describe(VerbosityLevel::precise()))
            )->build(),
        ];
    }
}
