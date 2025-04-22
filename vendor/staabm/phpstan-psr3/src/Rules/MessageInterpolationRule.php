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

        if ($args[0]->value instanceof Node\Expr\BinaryOp\Concat) {
            return [RuleErrorBuilder::message('Using interpolated strings in log messages is potentially a security risk.')->identifier('psr3.concat')->build()];
        }
        if ($args[0]->value instanceof Node\Scalar\InterpolatedString) {
            return [RuleErrorBuilder::message('Using interpolated strings in log messages is potentially a security risk.')->identifier('psr3.interpolated')->build()];
        }

        return [];
    }

    private function isPsr3LikeCall(MethodReflection $methodReflection): bool
    {
        if ($methodReflection->getDeclaringClass()->is(\Psr\Log\LoggerInterface::class)) {
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
