<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use rex;
use rex_i18n;
use rex_sql;
use staabm\PHPStanDba\Ast\ExpressionFinder;
use staabm\PHPStanDba\PhpDoc\PhpDocUtil;

use function array_key_exists;
use function count;
use function in_array;

/**
 * @implements Rule<MethodCall>
 *
 * @see https://psalm.dev/docs/security_analysis/
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
    private $taintSinks = [
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

        if (!array_key_exists($methodCall->name->toLowerString(), $this->taintSinks)) {
            return [];
        }

        $callerType = $scope->getType($methodCall->var);
        if ($callerType->getObjectClassNames() !== [rex_sql::class]) {
            return [];
        }

        $argNo = $this->taintSinks[$methodCall->name->toLowerString()];
        $sqlExpression = $args[$argNo]->value;

        // we can't infer query strings from properties
        if ($sqlExpression instanceof Node\Expr\PropertyFetch) {
            return [];
        }

        if ($sqlExpression instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $queryStringExpression = $finder->findAssignmentExpression($sqlExpression);
            if ($queryStringExpression !== null) {
                $sqlExpression = $queryStringExpression;
            }
        }

        $rawValue = $this->findInsecureSqlExpr($sqlExpression, $scope);
        if ($rawValue !== null) {
            $description = $this->exprPrinter->printExpr($rawValue);

            return [
                RuleErrorBuilder::message(
                    'Possible SQL-injection in expression '. $description .'.')
                    ->tip('Consider use of more SQL-safe types, prepared statements, rex_sql::escape(), rex_sql::escapeIdentifier() or rex_sql::in().')
                    ->identifier('rexstan.rexSqlInjection')
                    ->build(),
            ];
        }

        return [];
    }

    private function findInsecureSqlExpr(Node\Expr $expr, Scope $scope, bool $resolveVariables = true): ?Node\Expr
    {
        if ($resolveVariables === true && $expr instanceof Node\Expr\Variable) {
            $finder = new ExpressionFinder();
            $assignExpr = $finder->findAssignmentExpression($expr);

            if ($assignExpr !== null) {
                return $this->findInsecureSqlExpr($assignExpr, $scope);
            }

            return $this->findInsecureSqlExpr($expr, $scope, false);
        }

        if ($expr instanceof Concat) {
            $left = $expr->left;
            $right = $expr->right;

            $leftInsecure = $this->findInsecureSqlExpr($left, $scope);
            if ($leftInsecure !== null) {
                return $leftInsecure;
            }

            $rightInsecure = $this->findInsecureSqlExpr($right, $scope);
            if ($rightInsecure !== null) {
                return $rightInsecure;
            }

            return null;
        }

        if ($expr instanceof Node\Scalar\Encapsed) {
            foreach ($expr->parts as $part) {
                $insecurePart = $this->findInsecureSqlExpr($part, $scope);
                if ($insecurePart !== null) {
                    return $insecurePart;
                }
            }
            return null;
        }

        if ($expr instanceof Node\Scalar\EncapsedStringPart) {
            return null;
        }

        if ($expr instanceof Node\Expr\FuncCall && $expr->name instanceof Node\Name) {
            if (in_array($expr->name->toLowerString(), ['array_map'], true)) {
                $args = $expr->getArgs();

                $mappedType = $scope->getType($args[0]->value);
                if (!$mappedType->isCallable()->yes()) {
                    return $expr;
                }
                if ($this->isSafeCallable($mappedType, $scope)) {
                    return null;
                }

                return $expr;
            }
        }

        $exprType = $scope->getType($expr);
        $mixedType = new MixedType();
        if ($exprType->isSuperTypeOf($mixedType)->yes()) {
            return $expr;
        }

        if ($exprType->isString()->yes()) {
            if ($expr instanceof Node\Expr\CallLike) {
                if (PhpDocUtil::matchTaintEscape($expr, $scope) === 'sql') {
                    return null;
                }
            }

            if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name && $expr->name instanceof Node\Identifier) {
                // lets assume rex::getTable() and rex::getTablePrefix() return untainted values.
                // these methods are used in nearly every query and would otherwise create a lot of false positives.
                if ($expr->class->toString() === rex::class && in_array($expr->name->toLowerString(), ['gettableprefix', 'gettable'], true)) {
                    return null;
                }
                // translations could still lead to syntax errors, but since the input is not end-user controlled, we ignore it.
                if ($expr->class->toString() === rex_i18n::class && $expr->name->toLowerString() === 'msg') {
                    return null;
                }
            }

            if ($expr instanceof Node\Expr\FuncCall && $expr->name instanceof Node\Name) {
                if (in_array($expr->name->toLowerString(), ['implode', 'join'], true)) {
                    $args = $expr->getArgs();

                    if (count($args) >= 2) {
                        if ($args[1]->value instanceof Node\Expr\FuncCall) {
                            return $this->findInsecureSqlExpr($args[1]->value, $scope);
                        }

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

    private function isSafeType(Type $type): bool
    {
        if ($type->isLiteralString()->yes()) {
            return true;
        }

        if ($type->isNumericString()->yes()) {
            return true;
        }

        if ($type->isInteger()->yes()) {
            return true;
        }

        if ($type->isBoolean()->yes()) {
            return true;
        }

        if ($type->isFloat()->yes()) {
            return true;
        }

        return false;
    }

    private function isSafeCallable(Type $callableType, Scope $scope): bool
    {
        if (!$callableType->isCallable()->yes()) {
            throw new ShouldNotHappenException();
        }

        if ($callableType instanceof ConstantArrayType) {
            $valueTypes = $callableType->getValueTypes();

            if (count($valueTypes) === 2) {
                [$objectType, $methodType] = $valueTypes;

                $classReflections = $objectType->getObjectClassReflections();
                $methodNames = $methodType->getConstantStrings();
                foreach ($classReflections as $classReflection) {
                    foreach ($methodNames as $methodStringType) {
                        $methodReflection = $classReflection->getMethod($methodStringType->getValue(), $scope);

                        if (PhpDocUtil::matchTaintEscape($methodReflection, $scope) !== 'sql') {
                            return false;
                        }
                    }
                }

                return true;
            }
        }

        $parameterAcceptors = $callableType->getCallableParametersAcceptors($scope);
        if (count($parameterAcceptors) !== 1) {
            return false;
        }
        if (!$this->isSafeType($parameterAcceptors[0]->getReturnType())) {
            return false;
        }
        return true;
    }
}
