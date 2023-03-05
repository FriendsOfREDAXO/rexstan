<?php

namespace rexstan;

use Exception;
use rex_addon;
use rex_dir;
use rex_file;
use rex_path;
use RuntimeException;
use staabm\PHPStanBaselineAnalysis\ResultPrinter;
use function array_key_exists;
use function dirname;

final class RexLint
{
    /**
     * @return array<string, list<array{type: string, line: int, message: string, normalizeMessage: string}>>
     */
    public static function runFromWeb()
    {
        $binary = self::linterBinPath();

        $pathToLint = self::getPathsToLint();
        $cmd = $binary.' '. implode(' ', $pathToLint) .' --json --no-progress --no-colors --exclude .git --exclude .svn --exclude .idea --exclude vendor --exclude node_modules';
        $output = RexCmd::execCmd($cmd, $stderrOutput, $exitCode);

        $jsonResult = json_decode($output, true);
        if (!is_array($jsonResult)) {
            throw new \Exception('Unexpected result from parallel-lint: '. $output);
        }

        if (array_key_exists('results', $jsonResult)) {
            $results = $jsonResult['results'];
            if (array_key_exists('errors', $results)) {
                $lintErrors = $results['errors'];

                $errorPerFile = [];
                foreach($lintErrors as $error) {
                    $file = $error['file'];

                    if (!array_key_exists($file, $errorPerFile)) {
                        $errorPerFile[$file] = [];
                    }
                    unset($error['file']);
                    $errorPerFile[$file][] = $error;
                }
                // @phpstan-ignore-next-line
                return $errorPerFile;
            }
        } else {
            throw new \Exception('Unexpected result from parallel-lint: '. $output);
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public static function getPathsToLint(): array {
        $pathToLint = [
            rex_path::src('addons/')
        ];
        $modulesDir = DeveloperAddonIntegration::getModulesDir();
        if ($modulesDir !== null) {
            $pathToLint[] = $modulesDir;
        }

        $templatesDir = DeveloperAddonIntegration::getTemplatesDir();
        if ($templatesDir !== null) {
            $pathToLint[] = $templatesDir;
        }
        return $pathToLint;
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
