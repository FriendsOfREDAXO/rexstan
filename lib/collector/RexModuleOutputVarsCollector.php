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
 * @implements Collector<FileNode, array<int, array{string, array<string, scalar>}>>
 */
final class RexModuleOutputVarsCollector implements Collector
{
    public const FILE_SUFFIX = '.output.php';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        if (false === strpos($scope->getFile(), self::FILE_SUFFIX)) {
            return null;
        }

        return RexModuleVarsReflection::matchOutputVars(\Safe\file_get_contents($scope->getFile()));
    }
}
