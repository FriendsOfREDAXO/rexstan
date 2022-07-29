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
     * @return array<string, array<mixed>>|string
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
     * @return void
     */
    public static function clearResultCache()
    {
        $phpstanBinary = self::phpstanBinPath();

        $cmd = $phpstanBinary .' clear-result-cache';
        $output = self::execCmd($cmd, $lastError);
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

        return $output;
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

    private static function phpstanConfigPath(): string
    {
        $path = realpath(__DIR__.'/../phpstan.neon');

        if (false === $path) {
            throw new \RuntimeException('phpstan config not found');
        }

        return $path;
    }
}
