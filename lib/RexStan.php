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

final class RexStan
{
    /**
     * @return string
     */
    public static function runFromCli()
    {
        $phpstanBinary = self::phpstanBinPath();
        $configPath = self::phpstanConfigPath(__DIR__.'/../phpstan.neon');

        $cmd = $phpstanBinary .' analyse -c '. $configPath;
        $output = RexCmd::execCmd($cmd, $stderrOutput, $exitCode);

        return $output;
    }

    /**
     * @return array<string, mixed>|string
     */
    public static function runFromWeb()
    {
        $phpstanBinary = self::phpstanBinPath();
        $configPath = self::phpstanConfigPath(__DIR__.'/../phpstan.neon');

        $cmd = $phpstanBinary .' analyse -c '. $configPath .' --error-format=json --no-progress';
        $output = RexCmd::execCmd($cmd, $stderrOutput, $exitCode);

        if ('' !== $output && '{' === $output[0]) {
            // return the analysis result as an array
            return json_decode($output, true);
        }

        if ('' == $output) {
            $output = $stderrOutput;
        }

        // return the error string as is
        return $output;
    }

    /**
     * @return void
     */
    public static function generateAnalysisBaseline() {
        $phpstanBinary = self::phpstanBinPath();
        $configPath = self::phpstanConfigPath(__DIR__.'/../phpstan.neon');
        $analysisBaselinePath = RexStanSettings::getAnalysisBaselinePath();

        $addon = rex_addon::get('rexstan');
        $dataDir = $addon->getDataPath();

        RexCmd::execCmd('cd '.$dataDir.' && '. $phpstanBinary .' analyse -c '. $configPath .' --generate-baseline '. $analysisBaselinePath .' --allow-empty-baseline', $stderrOutput, $exitCode);
        if (0 !== $exitCode) {
            throw new Exception('Unable to generate analysis baseline:'. $stderrOutput);
        }
    }

    /**
     * @return array<ResultPrinter::KEY_*, int>|null
     */
    public static function analyzeSummaryBaseline()
    {
        $phpstanBinary = self::phpstanBinPath();
        $analyzeBinary = self::phpstanBaselineAnalyzeBinPath();
        $graphBinary = self::phpstanBaselineGraphBinPath();
        $configPath = self::phpstanConfigPath(__DIR__.'/../phpstan-summary.neon');

        $addon = rex_addon::get('rexstan');
        $dataDir = $addon->getDataPath();
        $configSignature = RexStanUserConfig::getSignature();

        // generate a summary graph only once within X minutes
        // to reduce data consumption
        $currentTime = time();
        $currentTimeSlot = $currentTime - ($currentTime % (15 * 60));

        $summaryPath = $dataDir.$configSignature.DIRECTORY_SEPARATOR. $currentTimeSlot .'-summary.json';
        $baselineGlob = $dataDir.$configSignature.DIRECTORY_SEPARATOR.'*-summary.json';
        $htmlGraphPath = $dataDir.'baseline-graph.html';

        RexCmd::execCmd('cd '.$dataDir.' && '. $phpstanBinary .' analyse -c '. $configPath .' --generate-baseline --allow-empty-baseline', $stderrOutput, $exitCode);
        if (0 !== $exitCode) {
            throw new Exception('Unable to generate baseline:'. $stderrOutput);
        }

        $output = RexCmd::execCmd('cd '.$dataDir.' && '. $analyzeBinary .' *phpstan-baseline.neon --json', $stderrOutput, $exitCode);
        if (0 !== $exitCode) {
            throw new Exception('Unable to analyze baseline: '.$stderrOutput);
        }

        if (!is_file($summaryPath)) {
            rex_dir::create(dirname($summaryPath));
            rex_file::put($summaryPath, $output);

            $htmlOutput = RexCmd::execCmd('cd '.$dataDir.' && '. $graphBinary ." '". $baselineGlob ."'", $stderrOutput, $exitCode);
            if (0 !== $exitCode) {
                throw new Exception('Unable to graph baseline: '.$stderrOutput);
            }
            rex_file::put($htmlGraphPath, $htmlOutput);
        }

        // returns a json array
        if ('[' === $output[0]) {
            // return the analysis result as an array
            $array = json_decode($output, true);

            if (!array_key_exists(0, $array)) {
                throw new Exception('The baseline analysis result is not an array');
            }
            if (!array_key_exists('phpstan-baseline.neon', $array[0])) {
                throw new Exception('The baseline analysis result is not an array');
            }

            return $array[0]['phpstan-baseline.neon'];
        }

        return null;
    }

    /**
     * @return void
     */
    public static function clearResultCache()
    {
        $phpstanBinary = self::phpstanBinPath();

        $cmd = $phpstanBinary .' clear-result-cache';
        RexCmd::execCmd($cmd, $stderrOutput, $exitCode);
    }

    private static function phpstanBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
        } else {
            $path = RexCmd::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/phpstan');
        }

        if (false === $path) {
            throw new RuntimeException('phpstan binary not found');
        }

        return $path;
    }

    private static function phpstanBaselineAnalyzeBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan-baseline-analyze.bat');
        } else {
            $path = RexCmd::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/phpstan-baseline-analyze');
        }

        if (false === $path) {
            throw new RuntimeException('phpstan-baseline-analyze binary not found');
        }

        return $path;
    }

    private static function phpstanBaselineGraphBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan-baseline-graph.bat');
        } else {
            $path = RexCmd::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/phpstan-baseline-graph');
        }

        if (false === $path) {
            throw new RuntimeException('phpstan-baseline-graph binary not found');
        }

        return $path;
    }

    private static function phpstanConfigPath(string $pathToFile): string
    {
        $path = realpath($pathToFile);

        if (false === $path) {
            throw new RuntimeException(sprintf('phpstan config "%s" not found. This file is usually created while AddOn setup. Try re-install of rexstan.', $pathToFile));
        }

        return $path;
    }
}
