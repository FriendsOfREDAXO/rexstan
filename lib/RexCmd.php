<?php

namespace rexstan;

final class RexCmd {
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
}
