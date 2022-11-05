<?php

declare(strict_types=1);

namespace rexstan;

use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use rex_article;
use rex_article_slice;
use rex_category;
use rex_media;
use rex_user;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\QueryReflector;
use function count;

final class RexGetValueReflection
{
    /**
     * @param class-string $class
     */
    public function getValueType(
        Type $name,
        string $class
    ): ?Type {
        $names = TypeUtils::getConstantStrings($name);
        if (0 === count($names)) {
            return null;
        }

        switch ($class) {
            case rex_article_slice::class:
                $query = 'SELECT * FROM rex_article_slice';
                break;

            case rex_user::class:
                $query = 'SELECT * FROM rex_user';
                break;

            case rex_category::class:
            case rex_article::class:
                $query = 'SELECT * FROM rex_article';
                break;

            case rex_media::class:
                $query = 'SELECT * FROM rex_media';
                break;

            default:
                throw new ShouldNotHappenException('Unknown class ' . $class);
        }

        $queryReflection = new QueryReflection();
        $resultType = $queryReflection->getResultType($query, QueryReflector::FETCH_TYPE_ASSOC);
        $valueTypes = [];
        foreach ($names as $name) {
            if ($resultType instanceof ConstantArrayType && $resultType->hasOffsetValueType($name)->yes()) {
                $valueTypes[] = $resultType->getOffsetValueType($name);
            }
        }

        if (0 === count($valueTypes)) {
            return null;
        }

        if (1 === count($valueTypes)) {
            return $valueTypes[0];
        }

        return TypeCombinator::union(...$valueTypes);
    }
}
