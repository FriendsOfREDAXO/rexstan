<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_key_exists;

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
        $allInputValues = $node->get(RexModuleInputVarsCollector::class);
        $allOutputValues = $node->get(RexModuleOutputVarsCollector::class);

        $errors = [];
        foreach ($allInputValues as $inputFile => $inputValues) {
            $outputFile = str_replace(RexModuleInputVarsCollector::FILE_SUFFIX, RexModuleOutputVarsCollector::FILE_SUFFIX, $inputFile);

            if (!array_key_exists($outputFile, $allOutputValues)) {
                continue;
            }

            $outputValues = $allOutputValues[$outputFile];
            foreach ($inputValues[0] as [$varClass, $id]) {
                if (!$this->arrayContainsVar($outputValues[0], $varClass, $id)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Module "%s" contains input value "%s" which is not used in module output.',
                        str_replace(RexModuleInputVarsCollector::FILE_SUFFIX, '', basename($inputFile)),
                        $varClass.'['.$id.']',
                    ))->file($inputFile)->build();
                }
            }
        }

        foreach ($allOutputValues as $outputFile => $outputValues) {
            $inputFile = str_replace(RexModuleOutputVarsCollector::FILE_SUFFIX, RexModuleInputVarsCollector::FILE_SUFFIX, $outputFile);

            if (!array_key_exists($inputFile, $allInputValues)) {
                continue;
            }

            $inputValues = $allInputValues[$inputFile];
            foreach ($outputValues[0] as [$varClass, $id]) {
                if (!$this->arrayContainsVar($inputValues[0], $varClass, $id)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Module "%s" contains ouput value "%s" which is not used in module input.',
                        str_replace(RexModuleInputVarsCollector::FILE_SUFFIX, '', basename($inputFile)),
                        $varClass.'['.$id.']',
                    ))->file($inputFile)->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @param list<array{class-string, int}>  $values
     * @param class-string $varClass
     * @return bool
     */
    private function arrayContainsVar(array $values, string $varClass, int $id)
    {
        foreach ($values as [$_varClass, $_id]) {
            if ($_varClass === $varClass && $_id === $id) {
                return true;
            }
        }
        return false;
    }
}
