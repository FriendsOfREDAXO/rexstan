<?php

final class RexStan
{
    /**
     * @return string
     */
    public static function runFromCli()
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
        } else {
            $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
        }
        $configPath = realpath(__DIR__.'/../phpstan.neon');

        $cmd = $phpstanBinary .' analyse -c '. $configPath;
        $output = self::execCmd($cmd, $lastError);

        return $output;
    }

    /**
     * @return array|string
     */
    public static function runFromWeb()
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
        } else {
            $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
        }
        $configPath = realpath(__DIR__.'/../phpstan.neon');

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
     * @param string $lastError
     * @return string
     */
    public static function execCmd(string $cmd, &$lastError)
    {
        $lastError = '';
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
}
