<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application;

use Dogma\Application\Colors as C;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Nette\Neon\Neon;
use stdClass;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_pad;
use function error_get_last;
use function explode;
use function file_get_contents;
use function getopt;
use function implode;
use function is_array;
use function is_bool;
use function is_file;
use function is_numeric;
use function is_string;
use function parse_ini_file;
use function sprintf;
use function substr;
use function trigger_error;

final class Configurator extends stdClass
{
    use StrictBehaviorMixin;

    private const HELP_COLUMN_WIDTH = 32;

    public const FLAG = 'flag';
    public const FLAG_VALUE = 'flag-value';
    public const VALUE = 'value';
    public const VALUES = 'values';
    public const ENUM = 'enum';
    public const SET = 'set';
    public const MAP = 'map';

    /** @var string[]|string[][] */
    private $arguments;

    /** @var mixed[] */
    private $defaults;

    /** @var mixed[] */
    private $values = [];

    /** @var mixed[] */
    private $profiles = [];

    /**
     * @param string[]|string[][] $arguments
     * @param mixed[] $defaults
     */
    public function __construct(array $arguments, array $defaults = [])
    {
        $this->arguments = $arguments;
        $this->defaults = $defaults;
    }

    public function hasValues(): bool
    {
        foreach ($this->values as $value) {
            if ($value !== null) {
                return true;
            }
        }
        return false;
    }

    public function renderHelp(): string
    {
        $guide = '';
        foreach ($this->arguments as $name => $config) {
            if (is_string($config)) {
                $guide .= $config . "\n";
                continue;
            }
            $row = '';
            [$short, $type, $info, $hint, $values] = array_pad($config, 5, null);
            $row .= $short ? C::white('  -' . $short) : '    ';
            $row .= C::white(' --' . $name);
            if ($type === self::FLAG_VALUE || $type === self::VALUE || $type === self::VALUES || $type === self::ENUM || $type === self::SET) {
                $row .= C::gray($hint ? " <$hint>" : ' <value>');
            }
            $row = C::padString($row, self::HELP_COLUMN_WIDTH);
            $row .= ' ' . $info;
            if ($type === self::ENUM || $type === self::SET) {
                $row .= '; values: ' . implode('|', array_map([C::class, 'lyellow'], $values));
            }
            if (isset($this->defaults[$name])) {
                if ($type === self::VALUES || $type === self::SET) {
                    $row .= '; default: ' . implode(',', array_map(function ($value) {
                        return C::lyellow($this->format($value));
                    }, $this->defaults[$name]));
                } else {
                    $row .= '; default: ' . C::lyellow($this->format($this->defaults[$name]));
                }
            }
            $guide .= $row . "\n";
        }

        return $guide . "\n";
    }

    public function loadCliArguments(): void
    {
        $short = [];
        $shortAlt = [];
        $long = [];
        $longAlt = [];
        foreach ($this->arguments as $name => $config) {
            if (is_string($config)) {
                continue;
            }
            [$shortcut, $type] = $config;
            if ($type === self::FLAG_VALUE) {
                if ($shortcut) {
                    $short[] = $shortcut . '';
                    $shortAlt[] = $shortcut . ':';
                }
                $long[] = $name . '';
                $longAlt[] = $name . ':';
            } else {
                if ($shortcut) {
                    $short[] = $shortcut . ($type === self::FLAG ? '' : ':');
                }
                $long[] = $name . ($type === self::FLAG ? '' : ':');
            }
        }

        $values = getopt(implode('', $short), $long);
        if ($values === false) {
            throw new ShouldNotHappenException('Something is wrong! ^_^');
        }
        if ($shortAlt || $longAlt) {
            $altValues = getopt(implode('', $shortAlt), $longAlt);
            if ($altValues === false) {
                throw new ShouldNotHappenException('Something is wrong! ^_^');
            }
            $values = array_merge($values, $altValues);
        }
        foreach ($this->arguments as $name => [$shortcut, $type]) {
            if (is_numeric($name)) {
                continue;
            }

            $value = $values[$name] ?? $values[$shortcut] ?? null;
            if ($value === false) {
                $value = true;
            }

            $value = $this->normalize($value, $type);
            $values[$name] = $value;
            unset($values[$shortcut]);
        }
        $this->values = $values;
    }

    public function loadConfig(string $filePath): void
    {
        if (is_file($filePath)) {
            if (substr($filePath, -5) === '.neon') {
                $file = file_get_contents($filePath);
                if ($file === false) {
                    /** @var string[] $error */
                    $error = error_get_last();
                    echo C::white(sprintf("Error while reading configuration file: %s!\n\n", $error['message']), C::RED);
                    exit(1);
                }
                $config = Neon::decode($file);
            } elseif (substr($filePath, -4) === '.ini') {
                $config = parse_ini_file($filePath);
            } else {
                echo C::white("Error: Only .neon and .ini files are supported!\n\n", C::RED);
                exit(1);
            }
        } elseif ($filePath) {
            echo C::white("Configuration file $filePath not found.\n\n", C::RED);
            exit(1);
        } else {
            $config = [];
        }

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $this->expandValues($config, $config[$key]);
            }
        }

        $this->loadValues($config, false);

        foreach ($config as $key => $value) {
            if ($key[0] === '@') {
                $this->profiles[$key] = $value;
            }
        }

        if ($this->values['use']) {
            foreach ($this->values['use'] as $use) {
                if (!isset($config[$use])) {
                    die("Configuration profile $use not found.");
                }
                $this->loadValues($config[$use], true);
            }
        }
    }

    /**
     * @param mixed[] $config
     * @param mixed[] $section
     */
    private function expandValues(array $config, array &$section): void
    {
        while (isset($section['include'])) {
            $includes = $section['include'];
            unset($section['include']);
            $includes = is_array($includes) ? $includes : [$includes];
            foreach ($includes as $include) {
                $section = array_merge($section, $config[$include]);
            }
        }
        foreach ($section as $key => $value) {
            if (is_array($value)) {
                $this->expandValues($config, $section[$key]);
            }
        }
    }

    /**
     * @param mixed[] $config
     */
    private function loadValues(array $config, bool $rewrite): void
    {
        foreach ($this->arguments as $name => [, $type]) {
            if (isset($config[$name]) && ($rewrite || !isset($this->values[$name]))) {
                $this->values[$name] = $this->normalize($config[$name]);
            }
        }
    }

    /**
     * @param string|string[]|float|int|bool|null $value
     * @return string|int|float|bool|string[]|int[]|float[]|null
     */
    private function normalize($value, ?string $type = null)
    {
        if (($type === self::VALUES || $type === self::SET) && is_string($value)) {
            $value = explode(',', $value);
            foreach ($value as &$item) {
                $item = $this->normalize($item);
            }
        } elseif (is_numeric($value)) {
            $value = (float) $value;
            if ($value === (float) (int) $value) {
                $value = (int) $value;
            }
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function format($value): string
    {
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }
        return (string) $value;
    }

    /**
     * @return ConfigurationProfile|mixed|null
     */
    public function __get(string $name)
    {
        if (isset($this->profiles['@' . $name])) {
            return new ConfigurationProfile($this->profiles['@' . $name]);
        }
        if (!array_key_exists($name, $this->values)) {
            trigger_error("Value '$name' not found.");
            return null;
        }
        if (!isset($this->values[$name]) && isset($this->defaults[$name])) {
            return $this->defaults[$name];
        } else {
            return $this->values[$name];
        }
    }

    public function __isset(string $name): bool
    {
        if (isset($this->profiles['@' . $name])) {
            return true;
        }
        if (!array_key_exists($name, $this->values)) {
            trigger_error("Value '$name' not found.");
            return false;
        }
        if (isset($this->values[$name])) {
            return true;
        } elseif (isset($this->defaults[$name])) {
            return true;
        }
        return false;
    }

}
