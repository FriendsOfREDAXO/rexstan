<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\PHPStanRules\NodeAnalyzer\AutowiredMethodPropertyAnalyzer;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Nette\Rules\ValidNetteInjectRule\ValidNetteInjectRuleTest
 */
final class ValidNetteInjectRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Property with @inject annotation or #[Nette\DI\Attributes\Inject] attribute must be public';
    /**
     * @var \Symplify\PHPStanRules\NodeAnalyzer\AutowiredMethodPropertyAnalyzer
     */
    private $autowiredMethodPropertyAnalyzer;

    public function __construct(AutowiredMethodPropertyAnalyzer $autowiredMethodPropertyAnalyzer)
    {
        $this->autowiredMethodPropertyAnalyzer = $autowiredMethodPropertyAnalyzer;
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $ruleErrors = [];

        $propertiesAndClassMethods = array_merge($node->getProperties(), $node->getMethods());

        foreach ($propertiesAndClassMethods as $propertyAndClassMethod) {
            if (! $this->autowiredMethodPropertyAnalyzer->detect($propertyAndClassMethod)) {
                continue;
            }

            if ($propertyAndClassMethod->isPublic()) {
                continue;
            }

            $ruleErrors[] = RuleErrorBuilder::message(self::ERROR_MESSAGE)
                ->line($propertyAndClassMethod->getLine())
                ->build();
        }

        return $ruleErrors;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\DI\Attributes\Inject;

class SomeClass
{
    #[Inject]
    private $someDependency;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Nette\DI\Attributes\Inject;

class SomeClass
{
    #[Inject]
    public $someDependency;
}
CODE_SAMPLE
            ),
        ]);
    }
}
