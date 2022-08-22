<?php

final class RexStanUserConfig
{
    /**
     * Dateinamen.
     */
    private const DEF_CONFIG = 'default-config.neon';
    private const USR_CONFIG = 'user-config.neon';

    /**
     * @var array<mixed>  Cache: Array mit der User-Konfiguration; null = noch nicht eingelesen
     */
    private static $userConf;

    /**
     * @var array<mixed>  Cache: Array mit der Default-Konfiguration; null = noch nicht eingelesen
     */
    private static $defaultConf = [];

    /**
     * @param array<string> $paths
     * @param array<string> $includes
     *
     * @return void
     */
    public static function save(int $level, array $paths, array $includes, int $phpVersion)
    {
        self::ensureUserConfLoaded();

        self::$userConf['includes'] = $includes;
        self::$userConf['parameters']['level'] = $level;
        self::$userConf['parameters']['paths'] = $paths;
        self::$userConf['parameters']['phpVersion'] = $phpVersion;

        $prefix = "# rexstan auto generated file - do not edit, rename or remove\n\n";

        rex_file::put(self::getUserConfigPath(), $prefix . rex_string::yamlEncode(self::$userConf, 3));
    }

    public static function getLevel(): int
    {
        self::ensureDefaultConfLoaded();
        self::ensureUserConfLoaded();
        return (int) (self::$userConf['parameters']['level'] ?? self::$defaultConf['parameters']['level'] );
    }

    public static function getPhpVersion(): int
    {
        self::ensureDefaultConfLoaded();
        self::ensureUserConfLoaded();
        return (int) (self::$userConf['parameters']['phpVersion'] ?? self::$defaultConf['parameters']['phpVersion']);
    }

    /**
     * @return array<string>
     */
    public static function getPaths(): array
    {
        self::ensureDefaultConfLoaded();
        self::ensureUserConfLoaded();
        return self::$userConf['parameters']['paths'] ?? self::$defaultConf['parameters']['paths'];
    }

    /**
     * @return array<string>
     */
    public static function getIncludes(): array
    {
        self::ensureDefaultConfLoaded();
        self::ensureUserConfLoaded();
        return self::$userConf['includes'] ?? self::$defaultConf['includes'];
    }

    /**
     * liefert den Pfadnamen der Default-Settings ('default-config.neon' im Addon-Verzeichnis).
     */
    private static function getDefaultConfigPath(): string
    {
        return rex_path::addon('rexstan', self::DEF_CONFIG);
    }

    /**
     * liefert den Pfadnamen der User-Settings ('user-config.neon' im Data-Verzeichnis).
     */
    private static function getUserConfigPath(): string
    {
        return rex_path::addonData('rexstan', self::USR_CONFIG);
    }

    /**
     * Stellt sicher, dass die User-Konfigurationsdatei geladen ist.
     */
    private static function ensureUserConfLoaded(): void
    {
        if (null === self::$userConf) {
            self::$userConf = rex_file::getConfig(self::getUserConfigPath(), []);
        }
    }

    /**
     * Stellt sicher, dass die Default-Konfigurationsdatei geladen ist.
     */
    private static function ensureDefaultConfLoaded(): void
    {
        if (null === self::$defaultConf) {
            self::$defaultConf = rex_file::getConfig(self::getDefaultConfigPath(), []);
        }
    }
}
