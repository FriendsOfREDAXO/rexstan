<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;
use rex_var;
use function get_class;

/**
 * @implements Collector<FileNode, array<int, array{class-string, string}>>
 */
final class RexModuleInputValueCollector implements Collector
{
    public const FILE_SUFFIX = '.input.php';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        if (false === strpos($scope->getFile(), self::FILE_SUFFIX)) {
            return null;
        }

        $it = rex_var::varsIterator(\Safe\file_get_contents($scope->getFile()));

        $vars = [];
        foreach ($it as $var) {
            $class = get_class($var);

            if (false === $class) {
                continue;
            }

            $vars[] = [
                $class,
                (string) $var->getArg('id', 0, true),
            ];
        }

        return $vars;
    }
}
