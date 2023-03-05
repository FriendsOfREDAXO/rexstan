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
        if (!method_exists(rex_template::class, 'exists')) {
            // BC
            return [];
        }

        $allTemplateVars = $node->get(RexTemplateVarsCollector::class);

        $errors = [];
        foreach ($allTemplateVars as $templateFile => $templateValues) {
            foreach ($templateValues[0] as [$varName, $args]) {
                if (array_key_exists('id', $args)) {
                    $id = (int) $args['id'];

                    if (!rex_template::exists($id)) {
                        $errors[] = RuleErrorBuilder::message(sprintf(
                            'Template "%s" includes invalid template by ID "%s"',
                            str_replace(RexTemplateVarsCollector::FILE_SUFFIX, '', basename($templateFile)),
                            $varName.'['.$id.']',
                        ))->file($templateFile)->build();
                    }
                } elseif (array_key_exists('key', $args)) {
                    $key = (string) $args['key'];

                    $template = rex_template::forKey($key);
                    if (null === $template) {
                        $errors[] = RuleErrorBuilder::message(sprintf(
                            'Template "%s" includes invalid template by key "%s"',
                            str_replace(RexTemplateVarsCollector::FILE_SUFFIX, '', basename($templateFile)),
                            $varName.'['.$key.']',
                        ))->file($templateFile)->build();
                    }
                }
            }
        }

        return $errors;
    }
}
