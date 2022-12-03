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
use function function_exists;
use function is_resource;
use function proc_open;

final class RexStan
{
    public static function phpExecutable(): string
    {
        if ('Darwin' === PHP_OS_FAMILY) {
            $executable = 'php';
            $customConfig = '/Library/Application Support/appsolute/MAMP PRO/conf/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION.'.ini';
            if (is_file($customConfig)) {
                $executable .= ' -c "'.$customConfig.'"';
            }

            $mampPhp = '/Applications/MAMP/bin/php/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION.'/bin/';
            if (is_executable($mampPhp.'php')) {
                return 'PATH="$PATH:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin:'.$mampPhp.'" '.$executable;
            }
        }
        return 'php';
    }

    /**
     * @return string
     */
    public static function runFromCli()
    {
        $phpstanBinary = self::phpstanBinPath();
        $configPath = self::phpstanConfigPath(__DIR__.'/../phpstan.neon');

        $cmd = $phpstanBinary .' analyse -c '. $configPath;
        $output = self::execCmd($cmd, $stderrOutput, $exitCode);

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
        $output = self::execCmd($cmd, $stderrOutput, $exitCode);

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
     * @return array<ResultPrinter::KEY_*, int>|null
     */
    public static function analyzeBaseline()
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

        self::execCmd('cd '.$dataDir.' && '. $phpstanBinary .' analyse -c '. $configPath .' --generate-baseline', $stderrOutput, $exitCode);
        if (0 !== $exitCode) {
            throw new Exception('Unable to generate baseline:'. $stderrOutput);
        }

        $output = self::execCmd('cd '.$dataDir.' && '. $analyzeBinary .' *phpstan-baseline.neon --json', $stderrOutput, $exitCode);
        if (0 !== $exitCode) {
            throw new Exception('Unable to analyze baseline: '.$stderrOutput);
        }

        if (!is_file($summaryPath)) {
            rex_dir::create(dirname($summaryPath));
            rex_file::put($summaryPath, $output);

            $htmlOutput = self::execCmd('cd '.$dataDir.' && '. $graphBinary ." '". $baselineGlob ."'", $stderrOutput, $exitCode);
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
        self::execCmd($cmd, $stderrOutput, $exitCode);
    }

    /**
     * @param string $stderrOutput
     * @param int $exitCode
     * @param-out string $stderrOutput
     * @param-out int $exitCode
     *
     * @return string
     */
    public static function execCmd(string $cmd, &$stderrOutput, &$exitCode)
    {
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],   // stderr
        ];

        $stderrOutput = '';
        $output = '';

        if (!function_exists('proc_open')) {
            throw new Exception('Function proc_open() is not available');
        }

        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stderrOutput = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $status = proc_get_status($process);
            if (false === $status) {
                throw new Exception('Unable to get process status');
            }
            while ($status['running']) {
                // sleep half a second
                usleep(500000);
                $status = proc_get_status($process);
                if (false === $status) {
                    throw new Exception('Unable to get process status');
                }
            }
            $exitCode = $status['exitcode'];

            proc_close($process);
        }

        return false === $output ? '' : $output;
    }

    private static function phpstanBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
        } else {
            $path = self::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/phpstan');
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
            $path = self::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/phpstan-baseline-analyze');
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
            $path = self::phpExecutable().' '.realpath(__DIR__.'/../vendor/bin/phpstan-baseline-graph');
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
