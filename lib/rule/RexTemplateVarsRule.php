<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use rex_template;
use rex_var_template;
use function array_key_exists;

/**
 * @implements Rule<CollectedDataNode>
 */
final class RexTemplateVarsRule implements Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $allTemplateVars = $node->get(RexTemplateVarsCollector::class);

        $errors = [];
        foreach ($allTemplateVars as $templateFile => $templateValues) {
            foreach ($templateValues[0] as [$varClass, $id, $key]) {
                if ($varClass === rex_var_template::class) {
                    $template = null;
                    if ($id !== 0) {
                        if (!rex_template::exists($id)) {
                            $errors[] = RuleErrorBuilder::message(sprintf(
                                'Template "%s" includes invalid template by ID "%s"',
                                str_replace(RexTemplateVarsCollector::FILE_SUFFIX, '', basename($templateFile)),
                                $varClass.'['.$id.']',
                            ))->file($templateFile)->build();
                        }
                    }

                    if ($key !== '') {
                        $template = rex_template::forKey($key);
                        if ($template === null) {
                            $errors[] = RuleErrorBuilder::message(sprintf(
                                'Template "%s" includes invalid template by key "%s"',
                                str_replace(RexTemplateVarsCollector::FILE_SUFFIX, '', basename($templateFile)),
                                $varClass.'['.$key.']',
                            ))->file($templateFile)->build();
                        }

                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param list<array{class-string, string}>  $values
     * @param class-string $varClass
     * @return bool
     */
    private function arrayContainsVar(array $values, string $varClass, string $id)
    {
        foreach ($values as [$_varClass, $_id]) {
            if ($_varClass === $varClass && $_id === $id) {
                return true;
            }
        }
        return false;
    }
}
