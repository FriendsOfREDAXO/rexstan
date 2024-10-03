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
    /**
     * @var array<string, string>
     */
    private $inOutMap = [
        'REX_INPUT_VALUE' => 'REX_VALUE',
    ];

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
            foreach ($inputValues[0] as [$varName, $args]) {
                if (!array_key_exists('id', $args)) {
                    continue;
                }
                $id = (int) $args['id'];

                if (!$this->arrayContainsVar($outputValues[0], $varName, $id)) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'Module "%s" contains input value "%s" which is not used in module output.',
                            str_replace(RexModuleInputVarsCollector::FILE_SUFFIX, '', basename($inputFile)),
                            $varName.'['.$id.']',
                        )
                    )->identifier('rexstan.rexModuleInputVar')->file($inputFile)->build();
                }
            }
        }

        foreach ($allOutputValues as $outputFile => $outputValues) {
            $inputFile = str_replace(RexModuleOutputVarsCollector::FILE_SUFFIX, RexModuleInputVarsCollector::FILE_SUFFIX, $outputFile);

            if (!array_key_exists($inputFile, $allInputValues)) {
                continue;
            }

            $inputValues = $allInputValues[$inputFile];
            foreach ($outputValues[0] as [$varName, $args]) {
                if (!array_key_exists('id', $args)) {
                    continue;
                }
                $id = (int) $args['id'];

                if (!$this->arrayContainsVar($inputValues[0], $varName, $id)) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Module "%s" contains ouput value "%s" which is not used in module input.',
                        str_replace(RexModuleInputVarsCollector::FILE_SUFFIX, '', basename($inputFile)),
                        $varName.'['.$id.']',
                    ))->identifier('rexstan.rexModuleOutputVar')->file($inputFile)->build();
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<int, array{string, array<string, scalar>}>  $values
     * @return bool
     */
    private function arrayContainsVar(array $values, string $varName, int $id)
    {
        if (array_key_exists($varName, $this->inOutMap)) {
            $varName = $this->inOutMap[$varName];
        }

        foreach ($values as [$_varName, $args]) {
            if (!array_key_exists('id', $args)) {
                continue;
            }
            $_id = (int) $args['id'];
            if ($_varName !== $varName) {
                continue;
            }
            if ($_id !== $id) {
                continue;
            }
            return true;
        }
        return false;
    }
}
