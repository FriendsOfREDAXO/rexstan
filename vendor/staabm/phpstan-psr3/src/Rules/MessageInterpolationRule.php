<?php

declare(strict_types=1);

namespace staabm\PHPStanPsr3\Rules;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

/**
 * @implements Rule<Node\Expr\CallLike>
 */
final class MessageInterpolationRule implements Rule
{
    /**
     * @var list<lowercase-string>
     */
    private array $psr3LogMethods = [
        'log',
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency',
    ];

    public function getNodeType(): string
    {
        return Node\Expr\CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $args = $node->getArgs();
        if (count($args) < 1) {
            return [];
        }

        $methodReflection = null;
        if ($node instanceof Node\Expr\MethodCall) {
            if (! $node->name instanceof Identifier) {
                return [];
            }

            $methodReflection = $scope->getMethodReflection($scope->getType($node->var), $node->name->toString());
        } elseif ($node instanceof Node\Expr\StaticCall) {
            if (! $node->name instanceof Identifier) {
                return [];
            }

            if ($node->class instanceof Name) {
                $classType = $scope->resolveTypeByName($node->class);
                $methodReflection = $scope->getMethodReflection($classType, $node->name->toString());
            }
        }

        if (
            $methodReflection === null
            || ! $this->isPsr3LikeCall($methodReflection)
        ) {
            return [];
        }

        if ($this->isRiskyExpr($args[0]->value, $scope)) {
            return [RuleErrorBuilder::message('Using interpolated strings in log messages is potentially a security risk. Use PSR-3 placeholders instead.')->identifier('psr3.interpolated')->build()];
        }

        return [];
    }

    private function isRiskyExpr(Node\Expr $expr, Scope $scope): bool
    {
        if ($expr instanceof Node\Expr\BinaryOp\Concat) {
            if ($this->isRiskyExpr($expr->left, $scope)) {
                return true;
            }
            if ($this->isRiskyExpr($expr->right, $scope)) {
                return true;
            }
            return false;
        }

        if ($expr instanceof Node\Scalar\InterpolatedString) {
            foreach ($expr->parts as $part) {
                if ($part instanceof Node\InterpolatedStringPart) {
                    continue;
                }
                if ($this->isRiskyExpr($part, $scope)) {
                    return true;
                }
            }
            return false;
        }

        if ($expr instanceof Node\Scalar) {
            return false;
        }

        return $this->isRiskyType($scope->getNativeType($expr));
    }

    private function isRiskyType(Type $type): bool
    {
        $safe = new UnionType([
            new FloatType(),
            new IntegerType(),
            new BooleanType(),
        ]);

        if ($safe->isSuperTypeOf($type)->yes()) {
            return false;
        }

        if ($type->isLiteralString()->yes() || $type->isEnum()->yes()) {
            return false;
        }

        return true;
    }

    private function isPsr3LikeCall(MethodReflection $methodReflection): bool
    {
        if (
            $methodReflection->getDeclaringClass()->is(\Psr\Log\LoggerInterface::class)
            || $methodReflection->getDeclaringClass()->implementsInterface(\Psr\Log\LoggerInterface::class)
        ) {
            return in_array(strtolower($methodReflection->getName()), $this->psr3LogMethods, true);
        }

        if ($methodReflection->getDeclaringClass()->is(\Illuminate\Support\Facades\Log::class)) {
            return in_array(strtolower($methodReflection->getName()), $this->psr3LogMethods, true);
        }

        if ($methodReflection->getDeclaringClass()->is(\rex_logger::class)) { // @phpstan-ignore class.notFound
            if (strtolower($methodReflection->getName()) === 'logerror') {
                return true;
            }
            return in_array(strtolower($methodReflection->getName()), $this->psr3LogMethods, true);
        }

        return false;
    }
}
