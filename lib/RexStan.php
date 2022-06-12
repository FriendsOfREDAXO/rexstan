<?php

final class RexStan {
    /**
     * @return array|string
     */
    static public function runFromCli() {
        $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
        $configPath = realpath(__DIR__.'/../phpstan.neon');

        $cmd = $phpstanBinary .' analyse -c '. $configPath;
        $output = shell_exec($cmd);

        return $output;
    }

    /**
     * @return array|string
     */
    static public function runFromWeb() {
        $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
        $configPath = realpath(__DIR__.'/../phpstan.neon');

        $cmd = 'REXSTAN_WEBUI=1 '. $phpstanBinary .' analyse -c '. $configPath .' --error-format=json --no-progress 2>&1';

        $output = shell_exec($cmd);
        if ($output[0] === '{') {
            // return the analysis result as an array
            return json_decode($output, true);
        }

        // return the error string as is
        return $output;
    }
}
