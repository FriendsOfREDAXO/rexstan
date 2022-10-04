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
use PHPStan\Type\Type;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
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

    /**
     * @var array<string, int>
     */
    private $methods = [
        'select' => 0,
        'setrawvalue' => 1,
        'setwhere' => 0,
        'preparequery' => 0,
        'setquery' => 0,
        'getarray' => 0,
        'setdbquery' => 0,
        'getdbarray' => 0,
    ];

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

        if (!array_key_exists($methodCall->name->toLowerString(), $this->methods)) {
            return [];
        }

        $callerType = $scope->getType($methodCall->var);
        if (!$callerType instanceof TypeWithClassName) {
            return [];
        }

        if (rex_sql::class !== $callerType->getClassname()) {
            return [];
        }

        $argNo = $this->methods[$methodCall->name->toLowerString()];
        $sqlExpression = $args[$argNo]->value;

        // we can't infer query strings from properties
        if ($sqlExpression instanceof Node\Expr\PropertyFetch) {
            return [];
        }

        if ($sqlExpression instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $queryStringExpression = $finder->findQueryStringExpression($sqlExpression);
            if ($queryStringExpression !== null) {
                $sqlExpression = $queryStringExpression;
            }
        }

        $rawValue = $this->findInsecureSqlExpr($sqlExpression, $scope);
        if (null !== $rawValue) {
            $description = $this->exprPrinter->printExpr($rawValue);

            return [
                RuleErrorBuilder::message(
                    'Possible SQL-injection in expression '. $description .'.')
                    ->tip('Consider use of more SQL-safe types, prepared statements or escape via rex_sql::escape*().')
                    ->build(),
            ];
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
                $insecurePart = $this->findInsecureSqlExpr($part, $scope);
                if (null !== $insecurePart) {
                    return $insecurePart;
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
                    if (rex_sql::class === $callerType->getClassName() && in_array($expr->name->toLowerString(), ['escape', 'escapeidentifier', 'escapelikewildcards', 'in'], true)) {
                        return null;
                    }
                }
            }

            if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name && $expr->name instanceof Node\Identifier) {
                if (rex::class === $expr->class->toString() && in_array($expr->name->toLowerString(), ['gettableprefix', 'gettable'], true)) {
                    return null;
                }
                if (rex_i18n::class === $expr->class->toString() && 'msg' === $expr->name->toLowerString()) {
                    return null;
                }
            }

            if ($expr instanceof Node\Expr\FuncCall && $expr->name instanceof Node\Name) {
                if (in_array($expr->name->toLowerString(), ['implode', 'join'], true)) {
                    $args = $expr->getArgs();

                    if (count($args) >= 2) {
                        $arrayValueType = $scope->getType($args[1]->value);

                        if ($arrayValueType->isArray()->yes() && $this->isSafeType($arrayValueType->getIterableValueType())) {
                            return null;
                        }
                    }
                }
            }

            if ($this->isSafeType($exprType)) {
                return null;
            }

            return $expr;
        }

        return null;
    }

    private function isSafeType(Type $type):bool {
        if ($type->isLiteralString()->yes()) {
            return true;
        }

        if ($type->isNumericString()->yes()) {
            return true;
        }

        $integer = new IntegerType();
        if ($integer->isSuperTypeOf($type)->yes()) {
            return true;
        }

        $bool = new BooleanType();
        if ($bool->isSuperTypeOf($type)->yes()) {
            return true;
        }

        $float = new FloatType();
        if ($float->isSuperTypeOf($type)->yes()) {
            return true;
        }

        return false;
    }
}
