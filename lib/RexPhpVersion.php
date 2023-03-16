<?php

namespace rexstan;

final class RexPhpVersion {
    static public function isEqualMajorMinor(int $version1, int $version2): bool {
        $v1 = (int) ($version1 / 100);
        $v2 = (int) ($version2 / 100);

        return $v1 === $v2;
    }

    static public function getCliVersion(): int {
        return shell_exec('php -r \'echo PHP_VERSION_ID;\'');
    }
}
