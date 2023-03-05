<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application;

use Dogma\StrictBehaviorMixin;
use Dogma\System\Os;
use function exec;
use function explode;
use function file_get_contents;
use function str_repeat;
use function trim;

class Console
{
    use StrictBehaviorMixin;

    /** @var bool */
    public $debug;

    public function write(string ...$params): self
    {
        foreach ($params as $param) {
            echo $param;
        }
        return $this;
    }

    public function writeLn(string ...$params): self
    {
        foreach ($params as $param) {
            echo $param;
        }

        $this->ln();

        return $this;
    }

    public function ln(int $rows = 1): self
    {
        if ($rows === 1) {
            echo "\n";
        } else {
            echo str_repeat("\n", $rows);
        }
        return $this;
    }

    public function writeFile(string $fileName): self
    {
        echo file_get_contents($fileName);

        return $this;
    }

    public function debugWrite(string ...$params): self
    {
        if (!$this->debug) {
            return $this;
        }

        return $this->write(...$params);
    }

    public function debugWriteLn(string ...$params): self
    {
        if (!$this->debug) {
            return $this;
        }

        return $this->writeLn(...$params);
    }

    public function debugLn(int $rows = 1): self
    {
        if (!$this->debug) {
            return $this;
        }

        return $this->ln($rows);
    }

    public static function switchTerminalToUtf8(): void
    {
        if (Os::isWindows()) {
            exec('chcp 65001');
        }
    }

    public static function getTerminalWidth(): int
    {
        static $columns;

        if ($columns) {
            return $columns;
        }

        if (Os::isWindows()) {
            exec('mode CON', $output);
            [, $columns] = explode(':', $output[4]);
            $columns = (int) trim($columns);
        } else {
            $columns = (int) exec('/usr/bin/env tput cols');
        }

        if (!$columns) {
            $columns = 80;
        }

        return $columns;
    }

}
