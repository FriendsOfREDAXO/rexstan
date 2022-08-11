<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class AutowiredMethodPropertyAnalyzer
{
    /**
     * @var string
     * @see https://regex101.com/r/gn2P0C/1
     */
    private const REQUIRED_DOCBLOCK_REGEX = '#\*\s+@(required|inject)\n?#';

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $stmt
     */
    public function detect($stmt): bool
    {
        $docComment = $stmt->getDocComment();
        if (! $docComment instanceof Doc) {
            return $this->hasAttributes(
                $stmt,
                ['Symfony\Contracts\Service\Attribute\Required', 'Nette\DI\Attributes\Inject']
            );
        }

        if ((bool) Strings::match($docComment->getText(), self::REQUIRED_DOCBLOCK_REGEX)) {
            return true;
        }

        return $this->hasAttributes(
            $stmt,
            ['Symfony\Contracts\Service\Attribute\Required', 'Nette\DI\Attributes\Inject']
        );
    }

    /**
     * @param string[] $desiredAttributeClasses
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property $stmt
     */
    private function hasAttributes($stmt, array $desiredAttributeClasses): bool
    {
        foreach ($stmt->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $attributeName = $attribute->name->toString();

                if (in_array($attributeName, $desiredAttributeClasses, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
