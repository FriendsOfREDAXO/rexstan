<?php

namespace rexstan;

use rex_addon;
use rex_file;
use rex_package;
use rex_path;
use rex_string;
use RuntimeException;

final class RexStanUserConfig
{
    /**
     * Cache key to invalidate the summary signature in case of default-config.neon changes.
     */
    private const DEFAULT_SETTINGS_VERSION = 'v2-type-coverage-only-in-summary';

    /**
     * @param list<string> $paths
     * @param list<string> $includes
     *
     * @return void
     */
    public static function save(int $level, array $paths, array $includes, int $phpVersion, bool $reportUnmatchedIgnoredErrors = false)
    {
        $scanDirectories = [];
        foreach (rex_package::getAvailablePackages() as $package) {
            $functionsPath = $package->getPath('functions/');
            if (is_dir($functionsPath)) {
                $scanDirectories[] = RexStanSettings::relativePath($functionsPath);
            }

            $vendorsPath = $package->getPath('vendor/');
            if (is_dir($vendorsPath)) {
                $scanDirectories[] = RexStanSettings::relativePath($vendorsPath);
            }
        }

        $file = [];
        $file['includes'] = $includes;
        $file['parameters']['level'] = $level;
        $file['parameters']['paths'] = $paths;
        $file['parameters']['scanDirectories'] = $scanDirectories;
        $file['parameters']['phpVersion'] = $phpVersion;
        $file['parameters']['reportUnmatchedIgnoredErrors'] = $reportUnmatchedIgnoredErrors;

        $prefix = "# rexstan auto generated file - do not edit, rename or remove\n\n";

        rex_file::put(self::getUserConfigPath(), $prefix . rex_string::yamlEncode($file, 3));
    }

    public static function isBaselineEnabled(): bool
    {
        $includes = self::getPhpStanConfig()['includes'];

        foreach ($includes as $include) {
            if (basename($include) === RexStanSettings::ANALYSIS_BASELINE_FILE) {
                return true;
            }
        }

        return false;
    }

    public static function getLevel(): int
    {
        return (int) self::getPhpStanConfig()['parameters']['level'];
    }

    public static function getPhpVersion(): int
    {
        return (int) self::getPhpStanConfig()['parameters']['phpVersion'];
    }

    /**
     * @return list<string>
     */
    public static function getPaths(): array
    {
        return self::getPhpStanConfig()['parameters']['paths'] ?? [];
    }

    /**
     * @return list<string>
     */
    public static function getIncludes(): array
    {
        return self::getPhpStanConfig()['includes'] ?? [];
    }

    /**
     * @return non-empty-string
     */
    public static function getSignature(): string
    {
        $md5 = md5_file(self::getUserConfigPath());
        return self::DEFAULT_SETTINGS_VERSION.'-'.$md5;
    }

    /**
     * @return array<string, mixed>
     */
    private static function getPhpStanConfig(): array
    {
        $neon = self::readUserConfig();
        $userConf = rex_string::yamlDecode($neon);

        $neon = rex_file::get(rex_path::addon('rexstan', 'default-config.neon'), '');
        $defaultConf = rex_string::yamlDecode($neon);

        return $userConf + $defaultConf;
    }

    private static function readUserConfig(): string
    {
        $neon = rex_file::get(self::getUserConfigPath());

        if ($neon === null) {
            throw new RuntimeException('Unable to read userconfig');
        }

        return $neon;
    }

    private static function getUserConfigPath(): string
    {
        return rex_addon::get('rexstan')->getDataPath('user-config.neon');
    }
}
