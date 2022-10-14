<?php

use function Safe\md5_file;

final class RexStanUserConfig
{
    /**
     * @param list<string> $paths
     * @param list<string> $includes
     *
     * @return void
     */
    public static function save(int $level, array $paths, array $includes, int $phpVersion)
    {
        $file = [];
        $file['includes'] = $includes;
        $file['parameters']['level'] = $level;
        $file['parameters']['paths'] = $paths;
        $file['parameters']['phpVersion'] = $phpVersion;

        $prefix = "# rexstan auto generated file - do not edit, rename or remove\n\n";

        rex_file::put(self::getUserConfigPath(), $prefix . rex_string::yamlEncode($file, 3));
    }

    public static function getLevel(): int
    {
        return (int) (self::getConfig()['parameters']['level']);
    }

    public static function getPhpVersion(): int
    {
        return (int) (self::getConfig()['parameters']['phpVersion']);
    }

    /**
     * @return list<string>
     */
    public static function getPaths(): array
    {
        return self::getConfig()['parameters']['paths'] ?? [];
    }

    /**
     * @return non-empty-string
     */
    public static function getSignature():string {
        return md5_file(self::getUserConfigPath());
    }

    /**
     * @return array<string, mixed>
     */
    private static function getConfig(): array
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

        if (null === $neon) {
            throw new \RuntimeException('Unable to read userconfig');
        }

        return $neon;
    }

    private static function getUserConfigPath(): string
    {
        return rex_addon::get('rexstan')->getDataPath('user-config.neon');
    }
}
