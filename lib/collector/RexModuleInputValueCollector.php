<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;
use rex_var;

/**
 * @implements Collector<FileNode, array<int, array{class-string, string}>>
 */
final class RexModuleInputValueCollector implements Collector {
    const FILE_SUFFIX = '.input.php';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        if (strpos($scope->getFile(), self::FILE_SUFFIX) === false) {
            return null;
        }

        $it = rex_var::varsIterator(\Safe\file_get_contents($scope->getFile()));

        $vars = [];
        foreach($it as $var) {
            $vars[] = [
                get_class($var),
                $var->getArg('id', 0, true)
            ];
        }
        return $vars;
    }
}
