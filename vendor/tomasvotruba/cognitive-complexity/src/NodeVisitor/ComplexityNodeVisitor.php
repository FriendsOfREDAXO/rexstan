<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TomasVotruba\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use TomasVotruba\CognitiveComplexity\NodeAnalyzer\ComplexityAffectingNodeFinder;

final class ComplexityNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @readonly
     */
    private CognitiveComplexityDataCollector $cognitiveComplexityDataCollector;

    /**
     * @readonly
     */
    private ComplexityAffectingNodeFinder $complexityAffectingNodeFinder;

    public function __construct(CognitiveComplexityDataCollector $cognitiveComplexityDataCollector, ComplexityAffectingNodeFinder $complexityAffectingNodeFinder)
    {
        $this->cognitiveComplexityDataCollector = $cognitiveComplexityDataCollector;
        $this->complexityAffectingNodeFinder = $complexityAffectingNodeFinder;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $this->complexityAffectingNodeFinder->isIncrementingNode($node)) {
            return null;
        }

        $this->cognitiveComplexityDataCollector->increaseOperation();

        return null;
    }
}
