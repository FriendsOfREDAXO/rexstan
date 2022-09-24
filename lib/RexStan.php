<?php

final class RexStan
{
    /**
     * @return string
     */
    public static function runFromCli()
    {
        $phpstanBinary = self::phpstanBinPath();
        $configPath = self::phpstanConfigPath();

        $cmd = $phpstanBinary .' analyse -c '. $configPath;
        $output = self::execCmd($cmd, $lastError);

        return $output;
    }

    /**
     * @return array<string, mixed>|string
     */
    public static function runFromWeb()
    {
        $phpstanBinary = self::phpstanBinPath();
        $configPath = self::phpstanConfigPath();

        $cmd = $phpstanBinary .' analyse -c '. $configPath .' --error-format=json --no-progress 2>&1';
        $output = self::execCmd($cmd, $lastError);

        if ('{' === $output[0]) {
            // return the analysis result as an array
            return json_decode($output, true);
        }

        if ('' == $output) {
            $output = $lastError;
        }

        // return the error string as is
        return $output;
    }

    /**
     * @return array{'Overall-Errors': int, 'Classes-Cognitive-Complexity': int, 'Deprecations': int, 'Invalid-Phpdocs': int, 'Unknown-Types': int, 'Anonymous-Variables': int}|null
     */
    public static function analyzeBaseline()
    {
        $phpstanBinary = self::phpstanBinPath();
        $analyzeBinary = self::phpstanBaselineAnalyzeBinPath();
        $configPath = self::phpstanConfigPath();

        $addon = rex_addon::get('rexstan');
        $dataDir = $addon->getDataPath();

        self::execCmd('cd '.$dataDir.' && '. $phpstanBinary .' analyse -c '. $configPath .' --generate-baseline', $lastError);
        $output = self::execCmd('cd '.$dataDir.' && '. $analyzeBinary .' *phpstan-baseline.neon --json', $lastError);
        // returns a json array
        if ('[' === $output[0]) {
            // return the analysis result as an array
            $array = json_decode($output, true);

            if (!array_key_exists(0, $array)) {
                throw new \Exception('The baseline analysis result is not an array');
            }
            if (!array_key_exists('phpstan-baseline.neon', $array[0])) {
                throw new \Exception('The baseline analysis result is not an array');
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
        self::execCmd($cmd, $lastError);
    }

    /**
     * @param string $lastError
     * @return string
     */
    public static function execCmd(string $cmd, &$lastError)
    {
        $lastError = '';
        // @phpstan-ignore-next-line
        set_error_handler(static function ($type, $msg) use (&$lastError) {
            $lastError = $msg;
        });
        try {
            $output = @shell_exec($cmd);
        } finally {
            restore_error_handler();
        }

        return $output ?? '';
    }

    private static function phpstanBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
        } else {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan');
        }

        if (false === $path) {
            throw new \RuntimeException('phpstan binary not found');
        }

        return $path;
    }

    private static function phpstanBaselineAnalyzeBinPath(): string
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan-baseline-analyze.bat');
        } else {
            $path = realpath(__DIR__.'/../vendor/bin/phpstan-baseline-analyze');
        }

        if (false === $path) {
            throw new \RuntimeException('phpstan-baseline-analyze binary not found');
        }

        return $path;
    }

    private static function phpstanConfigPath(): string
    {
        $path = realpath(__DIR__.'/../phpstan.neon');

        if (false === $path) {
            throw new \RuntimeException(sprintf('phpstan config "%s" not found. This file is usually created while AddOn setup. Try re-install of rexstan.', $path));
        }

        return $path;
    }
}
