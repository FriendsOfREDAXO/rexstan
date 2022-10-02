<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeWithClassName;
use rex_sql;
use function count;
use function in_array;

/**
 * @implements Rule<MethodCall>
 */
final class RexSqlInjectionRule implements Rule
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

        if (!in_array(strtolower($methodCall->name->toString()), ['setwhere', 'setquery'], true)) {
            return [];
        }

        $callerType = $scope->getType($methodCall->var);
        if (!$callerType instanceof TypeWithClassName) {
            return [];
        }

        if (rex_sql::class !== $callerType->getClassname()) {
            return [];
        }

        $sqlExpression = $args[0]->value;
        if ($this->containsRawValue($sqlExpression, $scope)) {
            return [
                RuleErrorBuilder::message('Possible SQL-injection: expression should instead use prepared statements or at least be escaped via rex_sql::escape().')
                    ->build(),
            ];
        }

        return [];
    }

    private function containsRawValue(Node\Expr $expr, Scope $scope): bool
    {
        if ($expr instanceof Concat) {
            $left = $expr->left;
            $right = $expr->right;

            return $this->containsRawValue($left, $scope) || $this->containsRawValue($right, $scope);
        }

        $exprType = $scope->getType($expr);
        $mixedType = new MixedType();
        if ($exprType->isSuperTypeOf($mixedType)->yes()) {
            return true;
        }

        if ($exprType->isString()->yes()) {
            if ($exprType->isLiteralString()->yes()) {
                return false;
            }

            if ($exprType->isNumericString()->yes()) {
                return false;
            }

            return true;
        }

        return false;
    }
}
