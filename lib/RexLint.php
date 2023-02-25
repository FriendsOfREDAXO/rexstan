<?php

namespace rexstan;

use Exception;
use rex_addon;
use rex_dir;
use rex_file;
use RuntimeException;
use staabm\PHPStanBaselineAnalysis\ResultPrinter;
use function array_key_exists;
use function dirname;

final class RexLint
{
    /**
     * @return list<array{type: string, file: string, line: int, message: string, normalizeMessage: string}>|null
     */
    public static function runFromWeb()
    {
        $binary = self::linterBinPath();

        $cmd = $binary.' '. \rex_path::src('addons/') .' --json --no-progress --no-colors --exclude .git --exclude .svn --exclude vendor';
        $output = RexCmd::execCmd($cmd, $stderrOutput, $exitCode);

        $jsonResult = json_decode($output, true);
        if (!is_array($jsonResult)) {
            return null;
        }

        if (array_key_exists('results', $jsonResult)) {
            $results = $jsonResult['results'];
            if (array_key_exists('errors', $results)) {
                return $results['errors'];
            }
        }

        return [];
    }

    private static function linterBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/parallel-lint.bat');
        } else {
            $path = RexCmd::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/parallel-lint');
        }

        if (false === $path) {
            throw new RuntimeException('parallel-lint binary not found');
        }

        return $path;
    }
}
