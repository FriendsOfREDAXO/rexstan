<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\CognitiveComplexity;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;

/**
 * @see \Symplify\PHPStanRules\Tests\CognitiveComplexity\CompositionOverInheritanceAnalyzer\CompositionOverInheritanceAnalyzerTest
 */
final class CompositionOverInheritanceAnalyzer
{
    /**
     * @var int
     */
    private const NON_FINAL_CLASS_SCORE = 10;

    /**
     * @var int
     */
    private const TRAIT_SCORE = 10;

    /**
     * @var int
     */
    private const INHERITANCE_CLASS_SCORE = 25;
    /**
     * @var \Symplify\PHPStanRules\CognitiveComplexity\AstCognitiveComplexityAnalyzer
     */
    private $astCognitiveComplexityAnalyzer;

    public function __construct(AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer)
    {
        $this->astCognitiveComplexityAnalyzer = $astCognitiveComplexityAnalyzer;
    }

    public function analyzeClassLike(Class_ $class): int
    {
        $totalCognitiveComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($class);

        // non final classes are more complex
        if (! $class->isFinal()) {
            $totalCognitiveComplexity += self::NON_FINAL_CLASS_SCORE;
        }

        // classes extending from another are more complex
        if ($class->extends !== null) {
            $totalCognitiveComplexity += self::INHERITANCE_CLASS_SCORE;
        }

        // classes using traits are more complex
        $totalCognitiveComplexity += $this->analyzeTraitUses($class);

        return $totalCognitiveComplexity;
    }

    private function analyzeTraitUses(Class_ $class): int
    {
        $traitComplexity = 0;

        foreach ($class->stmts as $stmt) {
            // trait-use can only appear as the very first statement in a class
            if ($stmt instanceof TraitUse) {
                $traitComplexity += count($stmt->traits) * self::TRAIT_SCORE;
            } else {
                break;
            }
        }

        return $traitComplexity;
    }
}
