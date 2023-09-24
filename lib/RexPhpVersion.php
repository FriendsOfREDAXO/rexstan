<?php

namespace rexstan;

final class RexPhpVersion
{
    /**
     * @param int|numeric-string $version2
     */
    public static function isEqualMajorMinor(int $version1, $version2): bool
    {
        $v1 = (int) ($version1 / 100);
        $v2 = (int) ($version2 / 100);

        return $v1 === $v2;
    }
}
