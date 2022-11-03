<?php

declare(strict_types=1);

namespace rexstan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;

final class RexModuleInputValueCollector implements Collector {
    const MODULE_INPUT_FILE_SUFFIX = '.input.php';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        if (strpos($scope->getFile(), self::MODULE_INPUT_FILE_SUFFIX) === false) {
            return null;
        }

        $x;
    }
}
