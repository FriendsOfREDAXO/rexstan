<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
use rex_article;
use rex_category;
use rex_media;
use rex_user;
use function count;
use function in_array;

/**
 * @implements Rule<CollectedDataNode>
 */
final class RexModuleVarsRule implements Rule
{

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $allInputValues = $node->get(RexModuleInputValueCollector::class);
        $allOutputValues = $node->get(RexModuleOutputValueCollector::class);

        $errors = [];
        foreach($allInputValues as $inputFile => $inputValues) {
            $outputFile = str_replace(RexModuleInputValueCollector::FILE_SUFFIX, RexModuleOutputValueCollector::FILE_SUFFIX, $inputFile);

            if (!array_key_exists($outputFile, $allOutputValues)) {
                continue;
            }

            $outputValues = $allOutputValues[$outputFile];
            foreach($inputValues[0] as [$varClass, $id]) {
                if (!$this->arrayContainsVar($outputValues[0], $varClass, $id))
                {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Module "%s" contains input value "%s" which is not used in module output.',
                        str_replace(RexModuleInputValueCollector::FILE_SUFFIX, '', basename($inputFile)),
                        $varClass.'['.$id.']',
                    ))->file($inputFile)->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @param list<array{class-string, string}>  $values
     * @param class-string string $varClass
     * @param string $id
     * @return bool
     */
    private function arrayContainsVar(array $values, string $varClass, string $id)
    {
        foreach($values as [$_varClass, $_id])
        {
            if ($_varClass === $varClass && $_id === $id) {
                return true;
            }
        }
        return false;
    }
}
