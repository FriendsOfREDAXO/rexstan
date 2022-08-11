<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Symplify\PHPStanRules\Enum\MethodName;
use Symplify\PHPStanRules\Nette\NetteInjectAnalyzer;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Nette\Rules\NoNetteInjectAndConstructorRule\NoNetteInjectAndConstructorRuleTest
 * @implements Rule<Class_>
 */
final class NoNetteInjectAndConstructorRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Use either __construct() or @inject, not both together';
    /**
     * @var \Symplify\PHPStanRules\Nette\NetteInjectAnalyzer
     */
    private $netteInjectAnalyzer;

    public function __construct(NetteInjectAnalyzer $netteInjectAnalyzer)
    {
        $this->netteInjectAnalyzer = $netteInjectAnalyzer;
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->isAbstract()) {
            return [];
        }

        $constructMethod = $node->getMethod(MethodName::CONSTRUCTOR);
        if (! $constructMethod instanceof ClassMethod) {
            return [];
        }

        foreach ($node->getMethods() as $classMethod) {
            if ($this->netteInjectAnalyzer->isInjectClassMethod($classMethod)) {
                return [self::ERROR_MESSAGE];
            }
        }

        foreach ($node->getProperties() as $property) {
            if ($this->netteInjectAnalyzer->isInjectProperty($property)) {
                return [self::ERROR_MESSAGE];
            }
        }

        return [];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $someType;

    public function __construct()
    {
        // ...
    }

    public function injectSomeType($someType)
    {
        $this->someType = $someType;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $someType;

    public function __construct($someType)
    {
        $this->someType = $someType;
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
