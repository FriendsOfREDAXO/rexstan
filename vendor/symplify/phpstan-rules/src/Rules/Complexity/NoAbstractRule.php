<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules\Complexity;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\TestCase;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\Complexity\NoAbstractRule\NoAbstractRuleTest
 */
final class NoAbstractRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Instead of abstract class, use specific service with composition';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class NormalHelper extends AbstractHelper
{
}

abstract class AbstractHelper
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class NormalHelper
{
    public function __construct(
        private SpecificHelper $specificHelper
    ) {
    }
}

final class SpecificHelper
{
}
CODE_SAMPLE
            ),
        ]);
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

        if (! $classLike->isAbstract()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        if ($classReflection->isSubclassOf(TestCase::class)) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }
}
