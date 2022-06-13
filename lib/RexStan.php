<?php

final class RexStan {
    /**
     * @return array|string
     */
    static public function runFromCli() {
        $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
        $configPath = realpath(__DIR__.'/../phpstan.neon');

        if (rex::getConsole()) {
            $cmd = 'REXSTAN_PATHFIX=1 '.$phpstanBinary .' analyse -c '. $configPath;
        } else {
            $cmd = $phpstanBinary .' analyse -c '. $configPath;
        }

        $output = shell_exec($cmd);

        return $output;
    }

    /**
     * @return array|string
     */
    static public function runFromWeb() {
        $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
        $configPath = realpath(__DIR__.'/../phpstan.neon');

        $cmd = 'REXSTAN_PATHFIX=1 '. $phpstanBinary .' analyse -c '. $configPath .' --error-format=json --no-progress 2>&1';

        $lastError = '';
        set_error_handler(function ($type, $msg) use (&$lastError) { $lastError = $msg; });
        try {
            $output = @shell_exec($cmd);
        } finally {
            restore_error_handler();
        }

        if ($output[0] === '{') {
            // return the analysis result as an array
            return json_decode($output, true);
        }

        if ($output == '') {
            $output = $lastError;
        }

        // return the error string as is
        return $output;
    }
}
