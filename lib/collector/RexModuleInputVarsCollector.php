<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;

/**
 * @implements Collector<FileNode, array<int, array{string, array<string, scalar>}>>
 */
final class RexModuleInputVarsCollector implements Collector
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

        return RexModuleVarsReflection::matchInputVars(\Safe\file_get_contents($scope->getFile()));
    }
}
