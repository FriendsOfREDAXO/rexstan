<?php

namespace rexstan;

final class RexPhpVersion
{
    /**
     * @param int|numeric-string $version1
     * @param int|numeric-string $version2
     */
    public static function isEqualMajorMinor($version1, $version2): bool
    {
        $version1 = (int) $version1;
        $version2 = (int) $version2;

        $v1 = (int) ($version1 / 100);
        $v2 = (int) ($version2 / 100);

        return $v1 === $v2;
    }
}
