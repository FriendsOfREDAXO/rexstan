<?php

declare(strict_types=1);

namespace FriendsOfRedaxo\RexStan;

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
        if (!str_contains($scope->getFile(), self::FILE_SUFFIX)) {
            return null;
        }

        return RexModuleVarsReflection::matchInputVars(\rex_file::get($scope->getFile(), ''));
    }
}
