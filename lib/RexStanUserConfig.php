<?php

final class RexStanUserConfig
{
    /**
     * @param array<string> $paths
     * @param array<string> $includes
     *
     * @return void
     */
    public static function save(int $level, array $paths, array $includes)
    {
        $file = [];
        $file['includes'] = $includes;
        $file['parameters']['level'] = $level;
        $file['parameters']['paths'] = $paths;

        $prefix = "# rexstan auto generated file - do not edit\n\n";

        rex_file::put(self::userconfig(), $prefix . rex_string::yamlEncode($file, 3));
    }

    public static function getLevel(): int
    {
        $neon = rex_file::get(self::userconfig());
        $settings = rex_string::yamlDecode($neon);
        return (int) $settings['parameters']['level'];
    }

    /**
     * @return array<string>
     */
    public static function getPaths(): array
    {
        $neon = rex_file::get(self::userconfig());
        $settings = rex_string::yamlDecode($neon);
        return $settings['parameters']['paths'];
    }

    /**
     * @return array<string>
     */
    public static function getIncludes(): array
    {
        $neon = rex_file::get(self::userconfig());
        $settings = rex_string::yamlDecode($neon);
        return $settings['includes'];
    }

    private static function userconfig(): string
    {
        return rex_addon::get('rexstan')->getDataPath('user-config.neon');
    }
}
