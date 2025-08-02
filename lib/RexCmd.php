<?php

namespace rexstan;

use Exception;

use function function_exists;
use function getenv;
use function is_resource;
use function posix_getpwuid;
use function posix_getuid;
use function proc_open;

final class RexCmd
{
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

            $stderrOutput = stream_get_contents($pipes[2]) ?: '';
            fclose($pipes[2]);

            $status = proc_get_status($process);
            while ($status['running']) {
                // sleep half a second
                usleep(500000);
                $status = proc_get_status($process);
            }
            $exitCode = $status['exitcode'];

            proc_close($process);
        }

        return $output === false ? '' : $output;
    }

    public static function phpExecutable(): string
    {
        $logger = new \rex_logger();
        $logger->log('debug', "phpExecutable");

        if ('Windows' !== PHP_OS_FAMILY) {
            $executable = 'php';
            $path = '$PATH:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin';

            if ('Darwin' === PHP_OS_FAMILY) {
                $customConfig = '/Library/Application Support/appsolute/MAMP PRO/conf/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION . '.ini';
                if (is_file($customConfig)) {
                    $executable .= ' -c "' . $customConfig . '"';
                }

                $mampPhp = '/Applications/MAMP/bin/php/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION . '/bin/';
                if (is_executable($mampPhp . 'php')) {
                    $path .= ':' . $mampPhp;
                }

                $homeDir = getenv('HOME') ?: posix_getpwuid(posix_getuid())['dir'];
                $herdPhp = $homeDir . '/Library/Application Support/Herd/bin/';
                if (is_executable($herdPhp . 'php' . PHP_MAJOR_VERSION . PHP_MINOR_VERSION)) {
                    $path .= ':' . $herdPhp;
                }
            }

            return 'PATH="' . $path . '" ' . $executable;
        }
        return 'php';
    }

    /**
     * @return null|numeric-string
     */
    public static function getCliPhpVersion(): ?string
    {

        $cliPhpVersion = self::execCmd(self::phpExecutable() . ' -r "echo PHP_VERSION_ID;"', $stderrOutput, $exitCode);

        if (is_numeric($cliPhpVersion)) {
            return $cliPhpVersion;
        }

        return null;
    }

    public static function getFormattedCliPhpVersion(): ?string
    {
        $cliPhpVersion = self::execCmd(self::phpExecutable() . ' -r "echo phpversion();"', $stderrOutput, $exitCode);

        if ($exitCode === 0) {
            return $cliPhpVersion;
        }

        return null;
    }
}
