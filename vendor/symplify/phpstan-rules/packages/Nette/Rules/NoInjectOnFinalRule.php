<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\PHPStanRules\Nette\NetteInjectAnalyzer;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Nette\Rules\NoInjectOnFinalRule\NoInjectOnFinalRuleTest
 * @implements Rule<InClassNode>
 */
final class NoInjectOnFinalRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Use constructor on final classes, instead of property injection';
    /**
     * @var \Symplify\PHPStanRules\Nette\NetteInjectAnalyzer
     */
    private $netteInjectAnalyzer;

    public function __construct(NetteInjectAnalyzer $netteInjectAnalyzer)
    {
        $this->netteInjectAnalyzer = $netteInjectAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            self::ERROR_MESSAGE,
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\DI\Attributes\Inject;

final class SomePresenter
{
     #[Inject]
    public $property;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Nette\DI\Attributes\Inject;

abstract class SomePresenter
{
    #[Inject]
    public $property;
}
CODE_SAMPLE
                ), ]
        );
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
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        if (! $classLike->isFinal()) {
            return [];
        }

        $errorMessage = [];

        foreach ($classLike->getProperties() as $property) {
            if (! $this->netteInjectAnalyzer->isInjectProperty($property)) {
                continue;
            }

            $errorMessage[] = RuleErrorBuilder::message(self::ERROR_MESSAGE)
                ->line($property->getLine())
                ->build();
        }

        return $errorMessage;
    }
}
