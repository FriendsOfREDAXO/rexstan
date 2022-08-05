<?php

final class RexStanPhpCli
{
    /**
     * @return numeric-string|null
     */
    public static function detectVersion()
    {
        $cliPhpVerssion = RexStan::execCmd('php -r "echo PHP_VERSION_ID;"', $lastError);
        if (is_numeric($cliPhpVerssion)) {
            return $cliPhpVerssion;
        }

        $latestVersionFolderName = RexStan::execCmd('command ls /Applications/MAMP/bin/php/ | sort -n | tail -1', $lastError);
        $mampPhpPath = '/Applications/MAMP/bin/php/'. $latestVersionFolderName .'/bin/php';
        if (is_file($mampPhpPath)) {
            $cliPhpVerssion = RexStan::execCmd($mampPhpPath.' -r "echo PHP_VERSION_ID;"', $lastError);
            if (is_numeric($cliPhpVerssion)) {
                return $cliPhpVerssion;
            }
        }

        return null;
    }
}
