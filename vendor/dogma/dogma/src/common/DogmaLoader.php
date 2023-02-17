<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use const DIRECTORY_SEPARATOR;
use function array_pop;
use function dirname;
use function explode;
use function glob;
use function implode;
use function is_dir;
use function is_file;
use function ltrim;
use function spl_autoload_register;
use function str_replace;
use function substr;

final class DogmaLoader
{
    use StrictBehaviorMixin;

    /** @var static */
    private static $instance;

    /** @var string[] */
    private $classMap = [];

    private function __construct()
    {
        $this->scan(__DIR__);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function register(bool $prepend = false): void
    {
        spl_autoload_register([$this, 'tryLoad'], true, $prepend);
    }

    public function tryLoad(string $class): void
    {
        $class = ltrim($class, '\\');
        if (isset($this->classMap[$class])) {
            require $this->classMap[$class];
            return;
        }

        if (substr($class, 0, 6) !== 'Dogma\\') {
            return;
        }

        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 5)) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }

        if (substr($class, -9) === 'Exception') {
            $parts = explode('\\', substr($class, 5));
            $last = array_pop($parts);
            $parts[] = 'exceptions';
            $parts[] = $last;
            $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getClassMap(): array
    {
        return $this->classMap;
    }

    private function scan(string $dir): void
    {
        foreach (glob($dir . '\\*') ?: [] as $path) {
            if (is_dir($path)) {
                $this->scan($path);
            } elseif (is_file($path)) {
                $parts = explode(DIRECTORY_SEPARATOR, str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
                $file = array_pop($parts);
                $class = substr($file, 0, -4);
                $this->classMap['Dogma\\' . $class] = $path;
            }
        }
    }

}
