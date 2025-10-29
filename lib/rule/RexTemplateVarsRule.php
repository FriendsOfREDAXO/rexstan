<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use rex_template;

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
            foreach ($templateValues[0] as [$varName, $args]) {
                if (array_key_exists('id', $args)) {
                    $id = (int) $args['id'];

                    if (!rex_template::exists($id)) {
                        $errors[] = RuleErrorBuilder::message(sprintf(
                            'Template "%s" includes invalid template by ID "%s"',
                            str_replace(RexTemplateVarsCollector::FILE_SUFFIX, '', basename($templateFile)),
                            $varName.'['.$id.']',
                        ))->identifier('rexstan.rexTemplateVar')->file($templateFile)->build();
                    }
                } elseif (array_key_exists('key', $args)) {
                    $key = (string) $args['key'];

                    $template = rex_template::forKey($key);
                    if ($template === null) {
                        $errors[] = RuleErrorBuilder::message(sprintf(
                            'Template "%s" includes invalid template by key "%s"',
                            str_replace(RexTemplateVarsCollector::FILE_SUFFIX, '', basename($templateFile)),
                            $varName.'['.$key.']',
                        ))->identifier('rexstan.rexTemplateVar')->file($templateFile)->build();
                    }
                }
            }
        }

        return $errors;
    }
}
