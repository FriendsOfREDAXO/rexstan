<?php

namespace rexstan;

use Exception;
use JsonSchema\Constraints\Constraint;
use PHPStan\ShouldNotHappenException;
use rex;
use rex_file;
use rex_package;
use rex_path;
use RuntimeException;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;

final class RexLint
{
    /**
     * @return array<string, list<array{line: int, message: string}>>
     */
    public static function runFromWeb()
    {
        $lintErrors = self::lintPaths();

        $jsonErrors = [];
        // package.json schema was bogus in earlier redaxo core versions
        if (rex_version::compare(rex::getVersion(), '5.15.2-dev', '>=')) {
            $jsonErrors = self::validateAddOnsPackageYml();
        }

        return array_merge($lintErrors, $jsonErrors);
    }

    /**
     * @return list<string>
     */
    public static function getPathsToLint(): array
    {
        $pathToLint = [
            rex_path::src('addons/'),
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

    /**
     * @return array<string, list<array{line: int, message: string}>>
     */
    private static function lintPaths(): array
    {
        $binary = self::linterBinPath();

        $pathToLint = self::getPathsToLint();
        $cmd = $binary.' '. implode(' ', $pathToLint) .' --json --no-progress --no-colors --exclude .git --exclude .svn --exclude .idea --exclude vendor --exclude node_modules';
        $output = RexCmd::execCmd($cmd, $stderrOutput, $exitCode);

        $jsonPhpLinterResult = json_decode($output, true);
        if (!is_array($jsonPhpLinterResult)) {
            throw new Exception('Unexpected result from parallel-lint: '. $output);
        }

        if (!array_key_exists('results', $jsonPhpLinterResult)) {
            throw new Exception('Unexpected result from parallel-lint: '.$output);
        }

        $results = $jsonPhpLinterResult['results'];
        if (!array_key_exists('errors', $results)) {
            return [];
        }

        $errorsPerFile = [];
        foreach ($results['errors'] as $error) {
            if (!is_string($error['file'])) {
                throw new ShouldNotHappenException();
            }
            if (!is_int($error['line'])) {
                throw new ShouldNotHappenException();
            }
            if (!is_string($error['message'])) {
                throw new ShouldNotHappenException();
            }

            $file = $error['file'];

            if (!array_key_exists($file, $errorsPerFile)) {
                $errorsPerFile[$file] = [];
            }

            $errorsPerFile[$file][] = [
                'line' => $error['line'],
                'message' => $error['message'],
            ];
        }

        return $errorsPerFile;
    }

    private static function linterBinPath(): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path = realpath(__DIR__.'/../vendor/bin/parallel-lint.bat');
        } else {
            $path = RexCmd::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/parallel-lint');
        }

        if ($path === false) {
            throw new RuntimeException('parallel-lint binary not found');
        }

        return $path;
    }

    /**
     * @return array<string, list<array{line: int, message: string}>>
     */
    private static function validateAddOnsPackageYml(): array
    {
        $packageSchema = rex_path::core('schemas/package.json');

        $errorsPerFile = [];
        foreach (rex_package::getAvailablePackages() as $package) {
            $packageYml = $package->getPath('package.yml');
            if (!is_file($packageYml)) {
                continue;
            }

            $jsonData = rex_file::getConfig($packageYml);
            foreach (self::validateJsonSchema($jsonData, $packageSchema) as $error) {
                if (!array_key_exists($packageYml, $errorsPerFile)) {
                    $errorsPerFile[$packageYml] = [];
                }

                $errorsPerFile[$packageYml][] = $error;
            }
        }

        return $errorsPerFile;
    }

    /**
     * @param array<mixed> $json
     *
     * @return list<array{line: int, message: string}>
     */
    private static function validateJsonSchema(array $json, string $schemaPath): array
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($json, (object) ['$ref' => 'file://'.$schemaPath], Constraint::CHECK_MODE_TYPE_CAST);

        $errors = [];
        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                if (strpos($error['message'], 'Failed to match all schemas') !== false) {
                    continue;
                }
                if (strpos($error['message'], 'Failed to match at least one schema') !== false) {
                    continue;
                }

                $errors[] = [
                    'line' => 0,
                    'message' => ($error['property'] ? $error['property'].' : ' : '').$error['message'],
                ];
            }
        }
        return $errors;
    }
}
