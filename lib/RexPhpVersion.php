<?php

namespace rexstan;

use RuntimeException;

final class RexPhpVersion
{
    public static function isEqualMajorMinor(int $version1, int $version2): bool
    {
        $v1 = (int) ($version1 / 100);
        $v2 = (int) ($version2 / 100);

        return $v1 === $v2;
    }

    public static function getCliVersion(): int
    {
        $output = RexCmd::execCmd('php -r "echo PHP_VERSION_ID;"', $stderrOutput, $exitCode);

        if (0 !== $exitCode) {
            throw new RuntimeException('Could not get PHP version from CLI: ' . $stderrOutput);
        }

        if (!is_numeric($output)) {
            throw new RuntimeException('Unexpected output: ' . $output);
        }

        return (int) $output;
    }
}
