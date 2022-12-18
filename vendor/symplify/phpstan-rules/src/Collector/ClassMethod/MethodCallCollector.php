<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Collector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;
use Symplify\PHPStanRules\Matcher\ClassMethodCallReferenceResolver;
use Symplify\PHPStanRules\ValueObject\MethodCallReference;

/**
 * @implements Collector<MethodCall, array<string>|null>
 * @deprecated
 */
final class MethodCallCollector implements Collector
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Symplify\PHPStanRules\Matcher\ClassMethodCallReferenceResolver
     */
    private $classMethodCallReferenceResolver;
    public function __construct(ReflectionProvider $reflectionProvider, ClassMethodCallReferenceResolver $classMethodCallReferenceResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->classMethodCallReferenceResolver = $classMethodCallReferenceResolver;
    }
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if ($node->name instanceof Expr) {
            return null;
        }

        $classMethodCallReference = $this->classMethodCallReferenceResolver->resolve($node, $scope, false);
        if (! $classMethodCallReference instanceof MethodCallReference) {
            return null;
        }

        $className = $classMethodCallReference->getClass();
        $methodName = $classMethodCallReference->getMethod();

        $classMethodReferences = $this->findParentClassMethodReferences($className, $methodName);
        $classMethodReferences[] = $className . '::' . $methodName;

        return $classMethodReferences;
    }

    /**
     * @return string[]
     */
    private function findParentClassMethodReferences(string $className, string $methodName): array
    {
        if (! $this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        $classMethodReferences = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasNativeMethod($methodName)) {
                $classMethodReferences[] = $parentClassReflection->getName() . '::' . $methodName;
            }
        }

        return $classMethodReferences;
    }
}
