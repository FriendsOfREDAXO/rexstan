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

        if (RexSqlReflection::getSqlResultType($methodCall, $scope) === null) {
            return [];
        }

        $offsetValueType = RexSqlReflection::getOffsetValueType($methodCall, $scope);
        if ($offsetValueType !== null) {
            if (strtolower($methodCall->name->toString()) === 'setarrayvalue' && !$offsetValueType->accepts(new StringType(), false)->yes()) {
                return [
                    RuleErrorBuilder::message(
                        'setArrayValue() expects a database column which can store string values.'
                    )->identifier('rexstan.rexSqlSetValue')->build(),
                ];
            }

            return [];
        }

        $valueNameType = $scope->getType($args[0]->value);
        $strings = $valueNameType->getConstantStrings();

        if (count($strings) === 0) {
            return [];
        }

        if (count($strings) === 1) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Value %s does not exist in table selected via setTable().', $valueNameType->describe(VerbosityLevel::precise()))
                )->identifier('rexstan.rexSqlSetValue')->build(),
            ];
        }

        return [
            RuleErrorBuilder::message(
                sprintf('All or one of the values %s was not selected in the used sql-query.', $valueNameType->describe(VerbosityLevel::precise()))
            )->identifier('rexstan.rexSqlSetValue')->build(),
        ];
    }
}
