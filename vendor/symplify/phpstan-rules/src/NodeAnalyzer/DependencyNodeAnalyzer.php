<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use Symplify\PHPStanRules\Enum\MethodName;

final class DependencyNodeAnalyzer
{
    /**
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @var \Symplify\PHPStanRules\NodeAnalyzer\AutowiredMethodPropertyAnalyzer
     */
    private $autowiredMethodPropertyAnalyzer;
    public function __construct(NodeFinder $nodeFinder, AutowiredMethodPropertyAnalyzer $autowiredMethodPropertyAnalyzer)
    {
        $this->nodeFinder = $nodeFinder;
        $this->autowiredMethodPropertyAnalyzer = $autowiredMethodPropertyAnalyzer;
    }

    public function isInsideAbstractClassAndPassedAsDependency(Property $property, Class_ $class): bool
    {
        if (! $class->isAbstract()) {
            return false;
        }

        $classMethod = $class->getMethod(MethodName::CONSTRUCTOR) ?? $class->getMethod(MethodName::SET_UP);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        /** @var Assign[] $assigns */
        $assigns = $this->nodeFinder->findInstanceOf($classMethod, Assign::class);
        if ($assigns === []) {
            return false;
        }

        return $this->isBeingAssignedInAssigns($property, $assigns);
    }

    public function isInsideClassAndAutowiredMethod(Property $property, Class_ $class): bool
    {
        $propertyProperty = $property->props[0];
        $propertyName = $propertyProperty->name->toString();

        foreach ($class->getMethods() as $classMethod) {
            /** @var PropertyFetch[] $propertyFetches */
            $propertyFetches = $this->nodeFinder->findInstanceOf($classMethod, PropertyFetch::class);

            foreach ($propertyFetches as $propertyFetch) {
                if (! $propertyFetch->name instanceof Identifier) {
                    continue;
                }

                if ($propertyFetch->name->toString() !== $propertyName) {
                    continue;
                }

                if ($this->autowiredMethodPropertyAnalyzer->detect($classMethod)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Assign[] $assigns
     */
    private function isBeingAssignedInAssigns(Property $property, array $assigns): bool
    {
        foreach ($assigns as $assign) {
            if (! $assign->var instanceof PropertyFetch) {
                continue;
            }

            if ($this->isPropertyFetchAndPropertyMatch($assign->var, $property)) {
                return true;
            }
        }

        return false;
    }

    private function isPropertyFetchAndPropertyMatch(PropertyFetch $propertyFetch, Property $property): bool
    {
        if (! $this->isLocalPropertyFetch($propertyFetch)) {
            return false;
        }

        if (! $propertyFetch->name instanceof Identifier) {
            return false;
        }

        $propertyProperty = $property->props[0];
        $assignedPropertyName = $propertyProperty->name->toString();

        return $propertyFetch->name->toString() === $assignedPropertyName;
    }

    private function isLocalPropertyFetch(PropertyFetch $propertyFetch): bool
    {
        if (! $propertyFetch->var instanceof Variable) {
            return false;
        }

        if (! is_string($propertyFetch->var->name)) {
            return false;
        }

        return $propertyFetch->var->name === 'this';
    }
}
