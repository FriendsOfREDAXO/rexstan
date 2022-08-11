<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\CognitiveComplexity\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use Symfony\Component\Console\Command\Command;
use Symplify\PHPStanRules\CognitiveComplexity\AstCognitiveComplexityAnalyzer;
use Symplify\PHPStanRules\CognitiveComplexity\CompositionOverInheritanceAnalyzer;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\CognitiveComplexity\Rules\ClassLikeCognitiveComplexityRule\ClassLikeCognitiveComplexityRuleTest
 */
final class ClassLikeCognitiveComplexityRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class cognitive complexity is %d, keep it under %d';
    /**
     * @var \Symplify\PHPStanRules\CognitiveComplexity\AstCognitiveComplexityAnalyzer
     */
    private $astCognitiveComplexityAnalyzer;
    /**
     * @var \Symplify\PHPStanRules\CognitiveComplexity\CompositionOverInheritanceAnalyzer
     */
    private $compositionOverInheritanceAnalyzer;
    /**
     * @var int
     */
    private $maxClassCognitiveComplexity = 50;
    /**
     * @var array<string, int>
     */
    private $limitsByTypes = [];
    /**
     * @var bool
     */
    private $scoreCompositionOverInheritance = false;

    /**
     * @param array<string, int> $limitsByTypes
     */
    public function __construct(AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer, CompositionOverInheritanceAnalyzer $compositionOverInheritanceAnalyzer, int $maxClassCognitiveComplexity = 50, array $limitsByTypes = [], bool $scoreCompositionOverInheritance = false)
    {
        $this->astCognitiveComplexityAnalyzer = $astCognitiveComplexityAnalyzer;
        $this->compositionOverInheritanceAnalyzer = $compositionOverInheritanceAnalyzer;
        $this->maxClassCognitiveComplexity = $maxClassCognitiveComplexity;
        $this->limitsByTypes = $limitsByTypes;
        $this->scoreCompositionOverInheritance = $scoreCompositionOverInheritance;
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        $classReflection = $node->getClassReflection();

        if ($this->scoreCompositionOverInheritance) {
            $measuredCognitiveComplexity = $this->compositionOverInheritanceAnalyzer->analyzeClassLike($classLike);
        } else {
            $measuredCognitiveComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($classLike);
        }

        $allowedCognitiveComplexity = $this->resolveAllowedCognitiveComplexity($classReflection);
        if ($measuredCognitiveComplexity <= $allowedCognitiveComplexity) {
            return [];
        }

        $message = sprintf(self::ERROR_MESSAGE, $measuredCognitiveComplexity, $allowedCognitiveComplexity);

        return [$message];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Cognitive complexity of class/trait must be under specific limit',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function simple($value)
    {
        if ($value !== 1) {
            if ($value !== 2) {
                return false;
            }
        }

        return true;
    }

    public function another($value)
    {
        if ($value !== 1 && $value !== 2) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function simple($value)
    {
        return $this->someOtherService->count($value);
    }

    public function another($value)
    {
        return $this->someOtherService->delete($value);
    }
}
CODE_SAMPLE
                ,
                [
                    'maxClassCognitiveComplexity' => 10,
                    'scoreCompositionOverInheritance' => true,
                ]
            ),
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

class SomeCommand extends Command
{
    public function configure()
    {
        $this->setName('...');
    }

    public function execute()
    {
        if (...) {
            // ...
        } else {
            // ...
        }
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;

class SomeCommand extends Command
{
    public function configure()
    {
        $this->setName('...');
    }

    public function execute()
    {
        return $this->externalService->resolve(...);
    }
}
CODE_SAMPLE
                    ,
                    [
                        'limitsByTypes' => [
                            Command::class => 5,
                        ],
                    ]
                ),

            ]
        );
    }

    private function resolveAllowedCognitiveComplexity(ClassReflection $classReflection): int
    {
        $className = $classReflection->getName();

        foreach ($this->limitsByTypes as $type => $limit) {
            if (is_a($className, $type, true)) {
                return $limit;
            }
        }

        return $this->maxClassCognitiveComplexity;
    }
}
