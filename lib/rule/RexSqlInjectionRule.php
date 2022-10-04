<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\ExprPrinter;
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
    /**
     * @var ExprPrinter
     */
    private $exprPrinter;

    public function __construct(
        ExprPrinter $exprPrinter
    ) {
        $this->exprPrinter = $exprPrinter;
    }

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

        if (!in_array(strtolower($methodCall->name->toString()), ['select', 'setwhere', 'setquery', 'setdbquery', 'preparequery', 'getarray', 'getdbarray'], true)) {
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

        // we can't infer query strings from properties
        if ($sqlExpression instanceof Node\Expr\PropertyFetch) {
            return [];
        }

        if ($sqlExpression instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $sqlExpression = $finder->findQueryStringExpression($sqlExpression);
        }

        if (null !== $sqlExpression) {
            $rawValue = $this->findInsecureSqlExpr($sqlExpression, $scope);
            if (null !== $rawValue) {
                $description = $this->exprPrinter->printExpr($rawValue);

                return [
                    RuleErrorBuilder::message(
                        'Possible SQL-injection in expression '. $description .'.')
                        ->tip('Consider use of more precise/safe types, prepared statements or escape via rex_sql::escape*().')
                        ->build(),
                ];
            }
        }

        return [];
    }

    private function findInsecureSqlExpr(Node\Expr $expr, Scope $scope, bool $resolveVariables = true): ?Node\Expr
    {
        if (true === $resolveVariables && $expr instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $assignExpr = $finder->findQueryStringExpression($expr);

            if (null !== $assignExpr) {
                return $this->findInsecureSqlExpr($assignExpr, $scope);
            }

            return $this->findInsecureSqlExpr($expr, $scope, false);
        }

        if ($expr instanceof Concat) {
            $left = $expr->left;
            $right = $expr->right;

            $leftInsecure = $this->findInsecureSqlExpr($left, $scope);
            if (null !== $leftInsecure) {
                return $leftInsecure;
            }

            $rightInsecure = $this->findInsecureSqlExpr($right, $scope);
            if (null !== $rightInsecure) {
                return $rightInsecure;
            }

            return null;
        }

        if ($expr instanceof Node\Scalar\Encapsed) {
            foreach ($expr->parts as $part) {
                if (null !== $this->findInsecureSqlExpr($part, $scope)) {
                    return $part;
                }
            }
            return null;
        }

        if ($expr instanceof Node\Scalar\EncapsedStringPart) {
            return null;
        }

        $exprType = $scope->getType($expr);
        $mixedType = new MixedType();
        if ($exprType->isSuperTypeOf($mixedType)->yes()) {
            return $expr;
        }

        if ($exprType->isString()->yes()) {
            if ($expr instanceof Node\Expr\CallLike) {
                if (PhpDocUtil::commentContains('@psalm-taint-escape sql', $expr, $scope)) {
                    return null;
                }
            }

            if ($expr instanceof Node\Expr\MethodCall && $expr->name instanceof Node\Identifier) {
                $callerType = $scope->getType($expr->var);

                if ($callerType instanceof TypeWithClassName) {
                    if (rex_sql::class === $callerType->getClassName() && in_array(strtolower($expr->name->toString()), ['escape', 'escapeidentifier', 'escapelikewildcards', 'in'], true)) {
                        return null;
                    }
                }
            }

            if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name && $expr->name instanceof Node\Identifier) {
                if (rex::class === $expr->class->toString() && in_array(strtolower($expr->name->toString()), ['gettableprefix', 'gettable'], true)) {
                    return null;
                }
                if (rex_i18n::class === $expr->class->toString() && 'msg' === strtolower($expr->name->toString())) {
                    return null;
                }
            }

            if ($exprType->isLiteralString()->yes()) {
                return null;
            }

            if ($exprType->isNumericString()->yes()) {
                return null;
            }

            return $expr;
        }

        return null;
    }
}
