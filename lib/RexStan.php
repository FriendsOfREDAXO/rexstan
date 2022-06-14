<?php

final class RexStan {
    /**
     * @return string
     */
    static public function runFromCli() {
       if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
           $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
       } else {
           $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
       }
       $configPath = realpath(__DIR__.'/../phpstan.neon');

        if (rex::getConsole()) {
            $pathFix = true;
        } else {
            $pathFix = false;
        }
        
        $cmd = $phpstanBinary .' analyse -c '. $configPath;
        $output = self::execCmd($cmd, $pathFix, $lastError);

        return $output;
    }

    /**
     * @return array|string
     */
    static public function runFromWeb() {
       if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
           $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan.bat');
       } else {
           $phpstanBinary = realpath(__DIR__.'/../vendor/bin/phpstan');
       }
        $configPath = realpath(__DIR__.'/../phpstan.neon');

        $cmd = $phpstanBinary .' analyse -c '. $configPath .' --error-format=json --no-progress 2>&1';
        $output = self::execCmd($cmd, true, $lastError);

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
    
    /**
     * @param string $lastError
     * @return string
     */
    static private function execCmd(string $cmd, bool $pathFix, &$lastError) {
        $lastError = '';
        set_error_handler(function ($type, $msg) use (&$lastError) { $lastError = $msg; });
        try {
           if ($pathFix) {
                // cross os compatible way of setting a env var.
                // the var will be inherited by the child process
                putenv('REXSTAN_PATHFIX=1');
           }
           $output = @shell_exec($cmd);
        } finally {
           if ($pathFix) {
                // remove the env var
                putenv('REXSTAN_PATHFIX');
           }
           restore_error_handler();
        }
        
        return $output;
    }
}
