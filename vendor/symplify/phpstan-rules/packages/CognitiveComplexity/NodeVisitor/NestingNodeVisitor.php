<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\CognitiveComplexity\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;
use Symplify\PHPStanRules\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use Symplify\PHPStanRules\CognitiveComplexity\NodeAnalyzer\ComplexityAffectingNodeFinder;

final class NestingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<class-string<Node>>
     */
    private const NESTING_NODE_TYPES = [
        If_::class,
        For_::class,
        While_::class,
        Catch_::class,
        Closure::class,
        Foreach_::class,
        Do_::class,
        Ternary::class,
    ];

    /**
     * @var int
     */
    private $measuredNestingLevel = 1;

    /**
     * @var int
     */
    private $previousNestingLevel = 0;
    /**
     * @var \Symplify\PHPStanRules\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector
     */
    private $cognitiveComplexityDataCollector;
    /**
     * @var \Symplify\PHPStanRules\CognitiveComplexity\NodeAnalyzer\ComplexityAffectingNodeFinder
     */
    private $complexityAffectingNodeFinder;
    public function __construct(CognitiveComplexityDataCollector $cognitiveComplexityDataCollector, ComplexityAffectingNodeFinder $complexityAffectingNodeFinder)
    {
        $this->cognitiveComplexityDataCollector = $cognitiveComplexityDataCollector;
        $this->complexityAffectingNodeFinder = $complexityAffectingNodeFinder;
    }

    public function reset(): void
    {
        $this->measuredNestingLevel = 1;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($this->isNestingNode($node)) {
            ++$this->measuredNestingLevel;
        }

        if (! $this->complexityAffectingNodeFinder->isIncrementingNode($node)) {
            return null;
        }

        if ($this->complexityAffectingNodeFinder->isBreakingNode($node)) {
            $this->previousNestingLevel = $this->measuredNestingLevel;
            return null;
        }

        // B2. Nesting level
        if ($this->measuredNestingLevel > 1 && $this->previousNestingLevel < $this->measuredNestingLevel) {
            // only going deeper, not on the same level
            $nestingComplexity = $this->measuredNestingLevel - 2;
            $this->cognitiveComplexityDataCollector->increaseNesting($nestingComplexity);
        }

        $this->previousNestingLevel = $this->measuredNestingLevel;

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($this->isNestingNode($node)) {
            --$this->measuredNestingLevel;
        }

        return null;
    }

    private function isNestingNode(Node $node): bool
    {
        foreach (self::NESTING_NODE_TYPES as $nestingNodeType) {
            if (is_a($node, $nestingNodeType, true)) {
                return true;
            }
        }

        return false;
    }
}
