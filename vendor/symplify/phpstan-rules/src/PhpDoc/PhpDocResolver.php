<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\PhpDoc;

use PhpParser\Comment\Doc;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\FileTypeMapper;

final class PhpDocResolver
{
    /**
     * @readonly
     */
    private FileTypeMapper $fileTypeMapper;
    public function __construct(FileTypeMapper $fileTypeMapper)
    {
        $this->fileTypeMapper = $fileTypeMapper;
    }

    public function resolve(Scope $scope, ClassReflection $classReflection, Doc $doc): ResolvedPhpDocBlock
    {
        return $this->fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            null,
            null,
            $doc->getText()
        );
    }
}
