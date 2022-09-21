<?php

declare(strict_types=1);

namespace redaxo\phpstan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use rex_article_slice;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use function count;
use function in_array;

final class RexArticleSliceGetValueDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return rex_article_slice::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(strtolower($methodReflection->getName()), ['getvalue'], true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $args = $methodCall->getArgs();
        if (1 < count($args)) {
            return null;
        }

        $nameType = $scope->getType($args[0]->value);
        if (!$nameType instanceof ConstantStringType) {
            return null;
        }

        $queryReflection = new QueryReflection();
        $resultType = $queryReflection->getResultType('SELECT * FROM rex_article_slice', QueryReflector::FETCH_TYPE_ASSOC);
        if ($resultType instanceof ConstantArrayType && $resultType->hasOffsetValueType($nameType)->yes()) {
            return $resultType->getOffsetValueType($nameType);
        }

        return null;
    }
}
