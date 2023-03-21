<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use rex_yform_manager_dataset;

/**
 * @implements Rule<StaticCall>
 */
final class YOrmDatasetOptionalTableRule implements Rule
{
    /**
     * the supported methods and the position of the table arg.
     *
     * @var array<string, int>
     */
    private const METHODS_CONFIG = [
        'create' => 0,
        'get' => 1,
        'require' => 1,
        'getraw' => 1,
        'getall' => 0,
        'query' => 0,
        'queryone' => 2,
        'querycollection' => 2
    ];

    /** @var ReflectionProvider */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Node\Name) {
            return [];
        }
        if (!$node->name instanceof Node\Identifier) {
            return [];
        }
        if (!class_exists(rex_yform_manager_dataset::class)) {
            return [];
        }

        $methodName = strtolower($node->name->name);
        if (!in_array($methodName, array_keys(self::METHODS_CONFIG), true)) {
            return [];
        }

        $isSubclass = false;
        $isMainClass = false;
        $className = $node->class->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->isSubclassOf(rex_yform_manager_dataset::class)) {
            $isSubclass = true;
        }
        if ($classReflection->getName() === rex_yform_manager_dataset::class) {
            $isMainClass = true;
        }

        if (
            (!$isMainClass && !$isSubclass)
            || ($isMainClass && $isSubclass)
        ) {
            return [];
        }

        $givenArgs = $node->getArgs();
        $tableArgPos = self::METHODS_CONFIG[$methodName];

        if ($isMainClass && !isset($givenArgs[$tableArgPos])) {
            return [RuleErrorBuilder::message(sprintf(
                'Method "%s" requires $table parameter, when invoked on %s base-class.',
                $node->name->name,
                rex_yform_manager_dataset::class
            ))->build()];
        }

        if ($isSubclass && isset($givenArgs[$tableArgPos])) {
            return [RuleErrorBuilder::message(sprintf(
                'Method "%s" should not be called with $table parameter, when invoked from a class extending %s.',
                $node->name->name,
                rex_yform_manager_dataset::class
            ))->build()];
        }

        return [];
    }
}
