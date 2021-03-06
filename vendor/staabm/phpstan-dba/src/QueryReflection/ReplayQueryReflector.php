<?php

declare(strict_types=1);

namespace staabm\PHPStanDba\QueryReflection;

use PHPStan\Type\Type;
use staabm\PHPStanDba\Error;

final class ReplayQueryReflector implements QueryReflector
{
    /**
     * @var ReflectionCache
     */
    private $reflectionCache;

    public function __construct(ReflectionCache $cache)
    {
        $this->reflectionCache = $cache;
    }

    public function validateQueryString(string $queryString): ?Error
    {
        return $this->reflectionCache->getValidationError($queryString);
    }

    public function getResultType(string $queryString, int $fetchType): ?Type
    {
        // queries with errors don't have a cached result type
        if (false === $this->reflectionCache->hasResultType($queryString, $fetchType)) {
            return null;
        }

        return $this->reflectionCache->getResultType($queryString, $fetchType);
    }
}
