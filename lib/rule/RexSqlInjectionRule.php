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
use rex;
use rex_i18n;
use rex_sql;
use staabm\PHPStanDba\Ast\ExpressionFinder;
use staabm\PHPStanDba\PhpDoc\PhpDocUtil;
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

        if (!in_array(strtolower($methodCall->name->toString()), ['setwhere', 'setquery', 'getarray', 'getdbarray'], true)) {
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

        if ($sqlExpression instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $sqlExpression = $finder->findQueryStringExpression($sqlExpression);
        }

        if (null !== $sqlExpression && $this->containsRawValue($sqlExpression, $scope)) {
            return [
                RuleErrorBuilder::message('Possible SQL-injection: expression should instead use prepared statements or at least be escaped via rex_sql::escape*().')
                    ->build(),
            ];
        }

        return [];
    }

    private function containsRawValue(Node\Expr $expr, Scope $scope, bool $resolveVariables = true): bool
    {
        if ($resolveVariables === true && $expr instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $assignExpr = $finder->findQueryStringExpression($expr);

            if (null !== $assignExpr) {
                return $this->containsRawValue($assignExpr, $scope);
            }

            return $this->containsRawValue($expr, $scope, false);
        }

        if ($expr instanceof Concat) {
            $left = $expr->left;
            $right = $expr->right;

            return $this->containsRawValue($left, $scope) || $this->containsRawValue($right, $scope);
        }

        if ($expr instanceof Node\Scalar\Encapsed) {
            foreach ($expr->parts as $part) {
                if (true === $this->containsRawValue($part, $scope)) {
                    return true;
                }
            }
            return false;
        }

        if ($expr instanceof Node\Scalar\EncapsedStringPart) {
            return false;
        }

        $exprType = $scope->getType($expr);
        $mixedType = new MixedType();
        if ($exprType->isSuperTypeOf($mixedType)->yes()) {
            return true;
        }

        if ($exprType->isString()->yes()) {
            if ($expr instanceof Node\Expr\MethodCall && $expr->name instanceof Node\Identifier) {
                $callerType = $scope->getType($expr->var);

                if ($callerType instanceof TypeWithClassName) {
                    if (rex_sql::class === $callerType->getClassName() && in_array(strtolower($expr->name->toString()), ['escape', 'escapeidentifier', 'escapelikewildcards', 'in'], true)) {
                        return false;
                    }
                }
            }

            if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name && $expr->name instanceof Node\Identifier) {
                if (PhpDocUtil::commentContains('@psalm-taint-escape sql', $expr, $scope)) {
                    return false;
                }

                if (rex_i18n::class === $expr->class->toString() && 'msg' === strtolower($expr->name->toString())) {
                    return false;
                }
            }

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
